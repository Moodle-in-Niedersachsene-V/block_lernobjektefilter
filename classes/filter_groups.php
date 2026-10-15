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
 * Keyword groups (school type, subject, level, material type, custom group) and their configured values.
 *
 * The configured values are the positive list for the search filter and for the AI answer;
 * the single reader for block, web service, tasks and AI.
 *
 * @package   block_lernobjektefilter
 * @copyright 2026 Moodle in Niedersachsen e. V.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter_groups {
    /** Setting name => group key, in display order. */
    public const GROUPS = [
        'schooltypes'   => 'schooltype',
        'subjects'      => 'subject',
        'levels'        => 'level',
        'materialtypes' => 'materialtype',
        'customvalues'  => 'custom',
    ];

    /** Maximum length of a keyword (length of the tag name field). */
    public const MAX_VALUE_LENGTH = 255;

    /**
     * Read the values of a keyword setting, one per line.
     *
     * @param string $setting Setting name (key of GROUPS).
     * @return string[] Non-empty, cleaned values without duplicates.
     */
    public static function values(string $setting): array {
        if (!isset(self::GROUPS[$setting])) {
            return [];
        }
        $lines = explode("\n", (string)get_config('block_lernobjektefilter', $setting));
        $values = [];
        foreach ($lines as $line) {
            $value = clean_param(trim($line), PARAM_TEXT);
            $key = \core_text::strtolower($value);
            // The first spelling of a value wins; later duplicates differing only in case are ignored.
            if ($value !== '' && \core_text::strlen($value) <= self::MAX_VALUE_LENGTH && !isset($values[$key])) {
                $values[$key] = $value;
            }
        }
        return array_values($values);
    }

    /**
     * The groups with at least one value, in display order.
     *
     * @return array[] Each with setting, key, label and values.
     */
    public static function groups(): array {
        $groups = [];
        foreach (self::GROUPS as $setting => $key) {
            $values = self::values($setting);
            if (!$values) {
                continue;
            }
            $groups[] = [
                'setting' => $setting,
                'key'     => $key,
                'label'   => self::label($key),
                'values'  => $values,
            ];
        }
        return $groups;
    }

    /**
     * Allowed values per setting, for the AI prompt.
     *
     * @return array Setting name => string[]; only groups with values.
     */
    public static function lists(): array {
        $lists = [];
        foreach (self::groups() as $group) {
            $lists[$group['setting']] = $group['values'];
        }
        return $lists;
    }

    /**
     * Label of a group; the custom group takes its label from the settings.
     *
     * @param string $key Group key.
     * @return string Plain text label.
     */
    public static function label(string $key): string {
        if ($key === 'custom') {
            $label = trim(clean_param((string)get_config('block_lernobjektefilter', 'customlabel'), PARAM_TEXT));
            return $label !== '' ? $label : get_string('label_custom', 'block_lernobjektefilter');
        }
        return get_string('label_' . $key, 'block_lernobjektefilter');
    }

    /**
     * Check search keywords against the configured values.
     *
     * @param string[] $tags Keywords from the client.
     * @return string[] The keywords in their configured spelling.
     * @throws \invalid_parameter_exception If a keyword is not configured or too many are given.
     */
    public static function validate_tags(array $tags): array {
        if (count($tags) > count(self::GROUPS)) {
            throw new \invalid_parameter_exception(get_string('error_invalidtag', 'block_lernobjektefilter'));
        }
        $allowed = [];
        foreach (self::groups() as $group) {
            foreach ($group['values'] as $value) {
                $allowed[\core_text::strtolower($value)] = $value;
            }
        }
        $valid = [];
        foreach ($tags as $tag) {
            $key = \core_text::strtolower(trim((string)$tag));
            if ($key === '') {
                continue;
            }
            if (!isset($allowed[$key])) {
                throw new \invalid_parameter_exception(get_string('error_invalidtag', 'block_lernobjektefilter'));
            }
            $valid[$key] = $allowed[$key];
        }
        return array_values($valid);
    }
}
