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

namespace block_lernobjektefilter\external;

use block_lernobjektefilter\filter_groups;
use block_lernobjektefilter\object_scope;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Web service: search public learning objects by category, activity type, keywords and search term.
 *
 * @package   block_lernobjektefilter
 * @copyright 2026 Moodle in Niedersachsen e. V.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search_objects extends external_api {
    /** Absolute server-side maximum for search results. */
    public const MAX_RESULT_LIMIT = 200;

    /** Result limit when the setting is missing or out of range (documented in the setting). */
    public const DEFAULT_RESULT_LIMIT = 100;

    /** Rate limit: maximum requests per time window and user. */
    public const RATE_LIMIT_REQUESTS = 30;

    /** Rate limit: time window in seconds. */
    public const RATE_LIMIT_WINDOW = 60;

    /** Maximum length of the description in a result. */
    public const INTRO_LENGTH = 250;

    /** Maximum length of the search term. */
    public const SEARCHTERM_LENGTH = 100;

    /**
     * Parameter definition.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'category'   => new external_value(PARAM_INT, 'Configured category ID with subcategories (0 = all)', VALUE_DEFAULT, 0),
            'modtype'    => new external_value(PARAM_PLUGIN, 'Activity type, e.g. scorm (empty = all)', VALUE_DEFAULT, ''),
            'tags'       => new external_multiple_structure(
                new external_value(PARAM_TEXT, 'Keyword from the configured lists'),
                'Keywords that must all be present',
                VALUE_DEFAULT,
                []
            ),
            'searchterm' => new external_value(PARAM_TEXT, 'Search term for name and description', VALUE_DEFAULT, ''),
            'limit'      => new external_value(PARAM_INT, 'Maximum number of results (capped by the server)', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Search public learning objects.
     *
     * @param int      $category   Category ID (0 = all configured).
     * @param string   $modtype    Activity type (empty = all configured).
     * @param string[] $tags       Keywords.
     * @param string   $searchterm Search term.
     * @param int      $limit      Requested maximum number of results.
     * @return array Matching learning objects and their count.
     */
    public static function execute(
        int $category = 0,
        string $modtype = '',
        array $tags = [],
        string $searchterm = '',
        int $limit = 0
    ): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'category'   => $category,
            'modtype'    => $modtype,
            'tags'       => $tags,
            'searchterm' => $searchterm,
            'limit'      => $limit,
        ]);

        // The search covers learning objects of several courses.
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('block/lernobjektefilter:search', $context);
        self::check_rate_limit();

        $records = self::find_objects(
            self::category_filter($params['category']),
            self::modtype_filter($params['modtype']),
            filter_groups::validate_tags($params['tags']),
            \core_text::substr(trim($params['searchterm']), 0, self::SEARCHTERM_LENGTH),
            self::effective_limit($params['limit'])
        );

        $objects = [];
        foreach ($records as $record) {
            $objects[] = self::object_result($record);
        }
        return ['objects' => $objects, 'total' => count($objects)];
    }

    /**
     * Return definition.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'objects' => new external_multiple_structure(
                new external_single_structure([
                    'cmid'         => new external_value(PARAM_INT, 'Course module ID'),
                    'name'         => new external_value(PARAM_TEXT, 'Name of the learning object'),
                    'modname'      => new external_value(PARAM_PLUGIN, 'Activity type'),
                    'modtypename'  => new external_value(PARAM_TEXT, 'Localised activity type'),
                    'iconurl'      => new external_value(PARAM_URL, 'Activity icon URL'),
                    'intro'        => new external_value(PARAM_RAW, 'Description as plain text, shortened'),
                    'coursename'   => new external_value(PARAM_TEXT, 'Course full name'),
                    'categoryname' => new external_value(PARAM_TEXT, 'Category path'),
                    'tags'         => new external_multiple_structure(new external_value(PARAM_TEXT, 'Keyword')),
                    'url'          => new external_value(PARAM_URL, 'URL of the learning object'),
                ])
            ),
            'total' => new external_value(PARAM_INT, 'Number of returned learning objects'),
        ]);
    }

    /**
     * Categories to search: a configured category with its configured subcategories, or all configured ones.
     *
     * @param int $categoryid Requested category (0 = all).
     * @return int[] Category IDs.
     * @throws \invalid_parameter_exception If the category is not configured.
     */
    private static function category_filter(int $categoryid): array {
        $allowed = object_scope::category_ids();
        if (!$categoryid) {
            return $allowed;
        }
        if (!in_array($categoryid, $allowed, true)) {
            throw new \invalid_parameter_exception(get_string('error_invalidcategory', 'block_lernobjektefilter'));
        }
        $category = \core_course_category::get($categoryid, MUST_EXIST, true);
        $ids = array_merge([$categoryid], array_map('intval', $category->get_all_children_ids()));
        return array_values(array_intersect($ids, $allowed));
    }

    /**
     * Activity types to search: a configured type or all configured ones.
     *
     * @param string $modtype Requested type (empty = all).
     * @return string[] Module names.
     * @throws \invalid_parameter_exception If the type is not configured.
     */
    private static function modtype_filter(string $modtype): array {
        $allowed = object_scope::modtypes();
        if ($modtype === '') {
            return $allowed;
        }
        if (!in_array($modtype, $allowed, true)) {
            throw new \invalid_parameter_exception(get_string('error_invalidmodtype', 'block_lernobjektefilter'));
        }
        return [$modtype];
    }

    /**
     * Cap the requested limit by the setting and the hard maximum.
     *
     * @param int $requested Requested limit (< 1 = setting).
     * @return int Limit to apply.
     */
    private static function effective_limit(int $requested): int {
        $configured = (int)get_config('block_lernobjektefilter', 'resultlimit');
        $configured = $configured < 1 ? self::DEFAULT_RESULT_LIMIT : min($configured, self::MAX_RESULT_LIMIT);
        return $requested < 1 ? $configured : min($requested, $configured);
    }

    /**
     * Query public learning objects matching all given filters, one query per activity type.
     *
     * @param int[]    $categoryids Categories to search.
     * @param string[] $modtypes    Activity types to search (configured, therefore valid table names).
     * @param string[] $tags        Keywords that must all be present.
     * @param string   $searchterm  Search term (empty = none).
     * @param int      $limit       Maximum number of records.
     * @return \stdClass[] Records sorted by name.
     */
    private static function find_objects(array $categoryids, array $modtypes, array $tags, string $searchterm, int $limit): array {
        global $DB;

        $found = [];
        foreach ($modtypes as $modname) {
            [$scopesql, $args] = object_scope::scope_sql([$modname], $categoryids);
            $conditions = [$scopesql];

            if ($searchterm !== '') {
                $conditions[] = '(' .
                    $DB->sql_like('i.name', ':st1', false) . ' OR ' .
                    $DB->sql_like('i.intro', ':st2', false) .
                ')';
                $term = '%' . $DB->sql_like_escape($searchterm) . '%';
                $args += ['st1' => $term, 'st2' => $term];
            }

            // Tag names are stored normalised (lower case) in tag.name.
            foreach (array_values($tags) as $idx => $tagname) {
                $conditions[] = "EXISTS (
                    SELECT 1 FROM {tag_instance} ti{$idx}
                    JOIN {tag} t{$idx} ON t{$idx}.id = ti{$idx}.tagid
                    WHERE ti{$idx}.component = 'core'
                      AND ti{$idx}.itemtype = 'course_modules'
                      AND ti{$idx}.itemid = cm.id
                      AND t{$idx}.name = :tag{$idx}
                )";
                $args["tag{$idx}"] = \core_text::strtolower($tagname);
            }

            // The activity table name comes from object_scope::modtypes(), a positive list of installed modules.
            $sql = 'SELECT cm.id AS cmid, cm.course, md.name AS modname, i.name, i.intro, i.introformat,
                           c.fullname AS coursename, c.category
                      FROM {course_modules} cm
                      JOIN {modules} md ON md.id = cm.module
                      JOIN {course} c ON c.id = cm.course
                      JOIN {' . $modname . '} i ON i.id = cm.instance
                     WHERE ' . implode(' AND ', $conditions) . '
                  ORDER BY i.name ASC, cm.id ASC';
            foreach ($DB->get_records_sql($sql, $args, 0, $limit) as $record) {
                $found[] = $record;
            }
        }

        \core_collator::asort_objects_by_property($found, 'name', \core_collator::SORT_NATURAL);
        return array_slice(array_values($found), 0, $limit);
    }

    /**
     * Build the result entry of one learning object.
     *
     * @param \stdClass $record Learning object record.
     * @return array Result entry.
     */
    private static function object_result(\stdClass $record): array {
        global $OUTPUT;

        $context = \context_module::instance($record->cmid);
        $coursecontext = \context_course::instance($record->course);
        $category = \core_course_category::get($record->category, IGNORE_MISSING, true);
        $intro = html_to_text(
            format_text($record->intro, $record->introformat, ['context' => $context, 'filter' => false]),
            0,
            false
        );
        if (\core_text::strlen($intro) > self::INTRO_LENGTH) {
            $intro = \core_text::substr($intro, 0, self::INTRO_LENGTH) . '…';
        }
        $component = 'mod_' . $record->modname;

        return [
            'cmid'         => (int)$record->cmid,
            'name'         => format_string($record->name, true, ['context' => $context]),
            'modname'      => $record->modname,
            'modtypename'  => get_string('modulename', $component),
            'iconurl'      => $OUTPUT->image_url('monologo', $component)->out(false),
            'intro'        => $intro,
            'coursename'   => format_string($record->coursename, true, ['context' => $coursecontext]),
            'categoryname' => $category ? $category->get_nested_name(false) : '',
            'tags'         => array_values(\core_tag_tag::get_item_tags_array('core', 'course_modules', $record->cmid)),
            'url'          => (new \moodle_url('/mod/' . $record->modname . '/view.php', ['id' => $record->cmid]))->out(false),
        ];
    }

    /**
     * Key of the rate limit bucket: the account, or the session for guests and visitors without an account.
     *
     * All guests share one guest account, so a bucket per account would let one visitor block all others.
     *
     * @return string Cache key.
     */
    private static function rate_limit_key(): string {
        global $USER;

        if (isloggedin() && !isguestuser()) {
            return 'rl_' . (int)$USER->id;
        }
        return 'rls_' . hash('sha256', session_id());
    }

    /**
     * Count the request of a visitor and refuse it above the rate limit.
     *
     * @throws \moodle_exception If the visitor exceeded the rate limit.
     */
    private static function check_rate_limit(): void {
        $cache = \cache::make('block_lernobjektefilter', 'ratelimit');
        $key = self::rate_limit_key();
        $now = time();
        $data = $cache->get($key);

        if ($data === false || ($now - $data['window_start']) >= self::RATE_LIMIT_WINDOW) {
            $cache->set($key, ['count' => 1, 'window_start' => $now]);
            return;
        }

        $data['count']++;
        $cache->set($key, $data);
        if ($data['count'] > self::RATE_LIMIT_REQUESTS) {
            throw new \moodle_exception('ratelimitexceeded', 'block_lernobjektefilter', '', (object)[
                'seconds' => self::RATE_LIMIT_WINDOW - ($now - $data['window_start']),
            ]);
        }
    }
}
