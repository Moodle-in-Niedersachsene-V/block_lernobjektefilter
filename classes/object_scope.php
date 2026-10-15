<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace block_lernobjektefilter;

/**
 * The rule "public learning object": the single source for search, preview, pool and AI tagging.
 *
 * A learning object is a visible course module of a configured activity type in a visible course
 * of a configured course category (optionally including its subcategories).
 *
 * @package   block_lernobjektefilter
 * @copyright 2026 Moodle in Niedersachsen e. V.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class object_scope {
    /** Activity types that are selected after installation, if installed. */
    public const DEFAULT_MODTYPES = ['book', 'folder', 'h5pactivity', 'imscp', 'lesson', 'page', 'resource', 'scorm', 'url'];

    /** Fields every activity table must have to be searchable. */
    public const REQUIRED_FIELDS = ['name', 'intro', 'introformat'];

    /**
     * Installed activity types that can be searched, for the setting.
     *
     * @return array Module name => localised module name.
     */
    public static function modtype_choices(): array {
        $choices = [];
        foreach (array_keys(\core_component::get_plugin_list('mod')) as $modname) {
            if (!self::is_searchable_modtype($modname)) {
                continue;
            }
            $choices[$modname] = get_string_manager()->string_exists('modulename', 'mod_' . $modname)
                ? get_string('modulename', 'mod_' . $modname)
                : $modname;
        }
        \core_collator::asort($choices);
        return $choices;
    }

    /**
     * Default selection of the activity type setting.
     *
     * @return array Module name => 1.
     */
    public static function default_modtypes(): array {
        $installed = \core_component::get_plugin_list('mod');
        $defaults = [];
        foreach (self::DEFAULT_MODTYPES as $modname) {
            if (isset($installed[$modname])) {
                $defaults[$modname] = 1;
            }
        }
        return $defaults;
    }

    /**
     * Configured activity types that are installed, enabled and searchable.
     *
     * The returned names come from a fixed positive list (installed module plugins),
     * so they may be used as table names in SQL.
     *
     * @return string[] Module names.
     */
    public static function modtypes(): array {
        global $DB;

        $configured = array_filter(explode(',', (string)get_config('block_lernobjektefilter', 'modtypes')), 'strlen');
        if (!$configured) {
            return [];
        }
        $installed = \core_component::get_plugin_list('mod');
        $enabled = $DB->get_records_menu('modules', ['visible' => 1], 'name', 'id, name');
        $modtypes = [];
        foreach ($configured as $modname) {
            $modname = clean_param(trim($modname), PARAM_PLUGIN);
            $usable = $modname !== '' && isset($installed[$modname]) && in_array($modname, $enabled, true);
            if ($usable && self::is_searchable_modtype($modname)) {
                $modtypes[] = $modname;
            }
        }
        sort($modtypes);
        return array_values(array_unique($modtypes));
    }

    /**
     * Configured course category IDs, with their subcategories if configured.
     *
     * @return int[] Category IDs.
     */
    public static function category_ids(): array {
        $configured = array_filter(array_map('intval', explode(',', (string)get_config('block_lernobjektefilter', 'categories'))));
        $withchildren = (bool)get_config('block_lernobjektefilter', 'includesubcategories');
        $ids = [];
        foreach ($configured as $categoryid) {
            $category = \core_course_category::get($categoryid, IGNORE_MISSING, true);
            if (!$category) {
                continue;
            }
            $ids[] = (int)$category->id;
            if ($withchildren) {
                $ids = array_merge($ids, array_map('intval', $category->get_all_children_ids()));
            }
        }
        sort($ids);
        return array_values(array_unique($ids));
    }

    /**
     * Whether categories and activity types are configured, so that the search can find anything.
     *
     * @return bool
     */
    public static function is_configured(): bool {
        return self::category_ids() && self::modtypes();
    }

    /**
     * The rule as SQL condition; expects the aliases cm (course_modules), md (modules) and c (course).
     *
     * @param string[]|null $modtypes Restrict to these configured types (null = all configured).
     * @param int[]|null $categoryids Restrict to these configured categories (null = all configured).
     * @return array [SQL condition, named parameters]; a condition that matches nothing if nothing is configured.
     */
    public static function scope_sql(?array $modtypes = null, ?array $categoryids = null): array {
        global $DB;

        $allowedtypes = self::modtypes();
        $allowedcats = self::category_ids();
        $modtypes = $modtypes === null ? $allowedtypes : array_values(array_intersect($modtypes, $allowedtypes));
        $categoryids = $categoryids === null ? $allowedcats : array_values(array_intersect($categoryids, $allowedcats));
        if (!$modtypes || !$categoryids) {
            return ['1 = 0', []];
        }

        [$catsql, $catparams] = $DB->get_in_or_equal($categoryids, SQL_PARAMS_NAMED, 'lofcat');
        [$typesql, $typeparams] = $DB->get_in_or_equal($modtypes, SQL_PARAMS_NAMED, 'loftype');
        $conditions = [
            'c.visible = 1',
            'c.id <> :lofsiteid',
            "c.category $catsql",
            'cm.visible = 1',
            'cm.deletioninprogress = 0',
            'md.visible = 1',
            "md.name $typesql",
        ];
        return [implode(' AND ', $conditions), ['lofsiteid' => SITEID] + $catparams + $typeparams];
    }

    /**
     * Look up a learning object that public visitors may use.
     *
     * @param int $cmid Course module ID.
     * @return \stdClass|null Record with id, course, instance, modname; null if not a public learning object.
     */
    public static function get_public_object(int $cmid): ?\stdClass {
        global $DB;

        [$scopesql, $params] = self::scope_sql();
        $sql = 'SELECT cm.id, cm.course, cm.instance, md.name AS modname
                  FROM {course_modules} cm
                  JOIN {modules} md ON md.id = cm.module
                  JOIN {course} c ON c.id = cm.course
                 WHERE cm.id = :cmid AND ' . $scopesql;
        $record = $DB->get_record_sql($sql, ['cmid' => $cmid] + $params);
        return $record ?: null;
    }

    /**
     * IDs of the courses that hold at least one public learning object.
     *
     * @return int[] Course IDs.
     */
    public static function course_ids(): array {
        global $DB;

        [$scopesql, $params] = self::scope_sql();
        $sql = 'SELECT DISTINCT c.id
                  FROM {course_modules} cm
                  JOIN {modules} md ON md.id = cm.module
                  JOIN {course} c ON c.id = cm.course
                 WHERE ' . $scopesql;
        return array_map('intval', $DB->get_fieldset_sql($sql, $params));
    }

    /**
     * Whether the activity table of a module has name and description, so that it can be searched.
     *
     * @param string $modname Module name.
     * @return bool
     */
    private static function is_searchable_modtype(string $modname): bool {
        global $DB;

        if ($modname !== clean_param($modname, PARAM_PLUGIN)) {
            return false;
        }
        $columns = $DB->get_columns($modname);
        foreach (self::REQUIRED_FIELDS as $field) {
            if (!isset($columns[$field])) {
                return false;
            }
        }
        return true;
    }
}
