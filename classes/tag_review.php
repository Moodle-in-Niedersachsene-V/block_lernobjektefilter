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
 * AI keyword suggestions: recording, review (apply or discard) and clean-up.
 *
 * Status of a suggestion: 'pending' (waiting for review), 'applied' (keywords added as tags),
 * 'discarded' (rejected in the review) or 'empty' (no value fits).
 *
 * @package   block_lernobjektefilter
 * @copyright 2026 Moodle in Niedersachsen e. V.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tag_review {
    /** Table of the suggestions. */
    public const TABLE = 'block_lernobjektefilter_sugg';

    /** Maximum number of pending suggestions shown on the review page. */
    public const PAGE_SIZE = 100;

    /**
     * Record the outcome of an AI suggestion; applies the keywords at once if requested.
     *
     * @param int $cmid Course module ID.
     * @param string[] $tags Suggested keywords.
     * @param bool $autoapply Whether suggestions become tags at once.
     * @return string Recorded status.
     */
    public static function record(int $cmid, array $tags, bool $autoapply): string {
        global $DB;

        if (!$tags) {
            $status = 'empty';
        } else if ($autoapply) {
            self::add_tags($cmid, $tags);
            $status = 'applied';
        } else {
            $status = 'pending';
        }

        $now = time();
        $DB->insert_record(self::TABLE, [
            'cmid'         => $cmid,
            'tags'         => json_encode(array_values($tags), JSON_UNESCAPED_UNICODE),
            'status'       => $status,
            'timecreated'  => $now,
            'timemodified' => $now,
        ]);
        return $status;
    }

    /**
     * Pending suggestions of learning objects that still exist, with the data for the review page.
     *
     * @return \stdClass[] Records with id, cmid, tags, modname, name, coursename.
     */
    public static function pending(): array {
        global $DB;

        $sql = "SELECT s.id, s.cmid, s.tags, cm.instance, md.name AS modname, c.fullname AS coursename, c.id AS courseid
                  FROM {block_lernobjektefilter_sugg} s
                  JOIN {course_modules} cm ON cm.id = s.cmid
                  JOIN {modules} md ON md.id = cm.module
                  JOIN {course} c ON c.id = cm.course
                 WHERE s.status = :status
              ORDER BY s.timecreated ASC, s.id ASC";
        $records = $DB->get_records_sql($sql, ['status' => 'pending'], 0, self::PAGE_SIZE);
        foreach ($records as $record) {
            // Module names come from the modules table, so they are valid table names.
            $record->name = (string)$DB->get_field($record->modname, 'name', ['id' => $record->instance]);
        }
        return $records;
    }

    /**
     * Apply a pending suggestion: its keywords become tags of the learning object.
     *
     * Keywords no longer configured are dropped; a vanished learning object discards the suggestion.
     *
     * @param int $id Suggestion ID.
     * @return bool Whether keywords were applied.
     */
    public static function apply(int $id): bool {
        global $DB;

        $suggestion = $DB->get_record(self::TABLE, ['id' => $id, 'status' => 'pending']);
        if (!$suggestion) {
            return false;
        }
        if (!$DB->record_exists('course_modules', ['id' => $suggestion->cmid])) {
            self::set_status($suggestion->id, 'discarded');
            return false;
        }
        $tags = self::still_allowed(self::decode($suggestion->tags));
        if ($tags) {
            self::add_tags((int)$suggestion->cmid, $tags);
        }
        self::set_status($suggestion->id, $tags ? 'applied' : 'empty');
        return (bool)$tags;
    }

    /**
     * Discard a pending suggestion.
     *
     * @param int $id Suggestion ID.
     * @return bool Whether a pending suggestion was discarded.
     */
    public static function discard(int $id): bool {
        global $DB;

        if (!$DB->record_exists(self::TABLE, ['id' => $id, 'status' => 'pending'])) {
            return false;
        }
        self::set_status($id, 'discarded');
        return true;
    }

    /**
     * Remove the suggestion of a deleted learning object.
     *
     * @param int $cmid Course module ID.
     */
    public static function forget(int $cmid): void {
        global $DB;

        $DB->delete_records(self::TABLE, ['cmid' => $cmid]);
    }

    /**
     * Add keywords as tags of the learning object (core tag area of course modules).
     *
     * @param int $cmid Course module ID.
     * @param string[] $tags Keywords.
     */
    private static function add_tags(int $cmid, array $tags): void {
        $context = \context_module::instance($cmid);
        foreach ($tags as $tag) {
            \core_tag_tag::add_item_tag('core', 'course_modules', $cmid, $context, $tag);
        }
    }

    /**
     * Keep only keywords that are still configured.
     *
     * @param string[] $tags Keywords.
     * @return string[] Keywords in their configured spelling.
     */
    private static function still_allowed(array $tags): array {
        $allowed = [];
        foreach (filter_groups::lists() as $values) {
            foreach ($values as $value) {
                $allowed[\core_text::strtolower($value)] = $value;
            }
        }
        $kept = [];
        foreach ($tags as $tag) {
            $key = \core_text::strtolower(trim($tag));
            if (isset($allowed[$key])) {
                $kept[$key] = $allowed[$key];
            }
        }
        return array_values($kept);
    }

    /**
     * Decode the stored keywords of a suggestion.
     *
     * @param string $stored JSON list of keywords.
     * @return string[] Keywords; empty if the value is not a JSON list.
     */
    public static function decode(string $stored): array {
        $tags = json_decode($stored, true);
        return is_array($tags) ? array_values(array_filter($tags, 'is_string')) : [];
    }

    /**
     * Change the status of a suggestion.
     *
     * @param int $id Suggestion ID.
     * @param string $status New status.
     */
    private static function set_status(int $id, string $status): void {
        global $DB;

        $DB->update_record(self::TABLE, (object)['id' => $id, 'status' => $status, 'timemodified' => time()]);
    }
}
