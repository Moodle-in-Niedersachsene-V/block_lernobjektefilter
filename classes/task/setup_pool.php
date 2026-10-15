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

use block_lernobjektefilter\pool_manager;

/**
 * Scheduled task: ensure the pool accounts exist and are enrolled in all courses holding learning objects.
 *
 * @package   block_lernobjektefilter
 * @copyright 2026 Moodle in Niedersachsen e. V.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setup_pool extends \core\task\scheduled_task {
    /**
     * Return the task name shown in the admin interface.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task_setup_pool', 'block_lernobjektefilter');
    }

    /**
     * Create missing pool accounts and enrol them.
     */
    public function execute(): void {
        pool_manager::ensure_role();
        $created = pool_manager::create_pool_accounts();
        $enrolled = pool_manager::enrol_pool_into_scope_courses();

        mtrace("Learning object filter pool: {$created} pool accounts created, {$enrolled} enrolments added.");
    }
}
