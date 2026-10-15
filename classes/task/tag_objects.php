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

namespace block_lernobjektefilter\task;

use block_lernobjektefilter\object_scope;
use block_lernobjektefilter\tag_review;
use block_lernobjektefilter\tag_suggester;

/**
 * Scheduled task: AI tagging of public learning objects that have no keyword suggestion yet.
 *
 * Each learning object is processed once; the outcome is kept in block_lernobjektefilter_sugg.
 * A failed request leaves no record, so the learning object is retried on the next run.
 *
 * @package   block_lernobjektefilter
 * @copyright 2026 Moodle in Niedersachsen e. V.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tag_objects extends \core\task\scheduled_task {
    /** Learning objects per run when the setting is missing (documented in the setting). */
    public const DEFAULT_BATCH_SIZE = 20;

    /** Hard maximum of learning objects per run. */
    public const MAX_BATCH_SIZE = 200;

    /**
     * Return the task name shown in the admin interface.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task_tag_objects', 'block_lernobjektefilter');
    }

    /**
     * Tag the next batch; a failed learning object does not stop the others, but fails the task.
     *
     * @throws \moodle_exception If AI tagging is enabled without an AI provider, or a learning object failed.
     */
    public function execute(): void {
        if (!get_config('block_lernobjektefilter', 'ai_enabled')) {
            mtrace('AI tagging is disabled in the learning object filter settings.');
            return;
        }
        if (!tag_suggester::is_available()) {
            throw new \moodle_exception('error_ai_unavailable', 'block_lernobjektefilter');
        }

        $autoapply = (bool)get_config('block_lernobjektefilter', 'ai_autoapply');
        $userid = (int)get_admin()->id;
        $batchsize = (int)get_config('block_lernobjektefilter', 'ai_batch_size');
        $batchsize = $batchsize < 1 ? self::DEFAULT_BATCH_SIZE : min($batchsize, self::MAX_BATCH_SIZE);
        $objects = self::next_objects($batchsize);

        $failed = 0;
        foreach ($objects as $object) {
            try {
                $tags = tag_suggester::suggest($object, $userid);
                $status = tag_review::record((int)$object->cmid, $tags, $autoapply);
                mtrace("Learning object {$object->cmid}: {$status} (" . implode(', ', $tags) . ')');
            } catch (\Throwable $e) {
                mtrace("Learning object {$object->cmid}: AI tagging failed: " . $e->getMessage());
                $failed++;
            }
        }

        if ($failed > 0) {
            throw new \moodle_exception('error_ai_tagging_failed', 'block_lernobjektefilter', '', (object)[
                'failed' => $failed,
                'total'  => count($objects),
            ]);
        }
    }

    /**
     * Public learning objects without a keyword suggestion yet, with name and description.
     *
     * @param int $limit Maximum number of learning objects.
     * @return \stdClass[] Records with cmid, modname, name, intro, introformat, coursename, categoryname.
     */
    private static function next_objects(int $limit): array {
        global $DB;

        [$scopesql, $params] = object_scope::scope_sql();
        $sql = 'SELECT cm.id AS cmid, cm.instance, md.name AS modname, c.fullname AS coursename, cc.name AS categoryname
                  FROM {course_modules} cm
                  JOIN {modules} md ON md.id = cm.module
                  JOIN {course} c ON c.id = cm.course
                  JOIN {course_categories} cc ON cc.id = c.category
             LEFT JOIN {block_lernobjektefilter_sugg} s ON s.cmid = cm.id
                 WHERE ' . $scopesql . ' AND s.id IS NULL
              ORDER BY cm.id ASC';
        $objects = $DB->get_records_sql($sql, $params, 0, $limit);

        foreach ($objects as $object) {
            // Module names come from object_scope::modtypes(), a positive list of installed modules.
            $instance = $DB->get_record($object->modname, ['id' => $object->instance], 'id, name, intro, introformat');
            $object->name = $instance ? $instance->name : '';
            $object->intro = $instance ? (string)$instance->intro : '';
            $object->introformat = $instance ? (int)$instance->introformat : FORMAT_HTML;
        }
        return $objects;
    }
}
