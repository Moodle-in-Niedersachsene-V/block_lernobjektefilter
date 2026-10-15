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

/**
 * Post-install hook for block_lernobjektefilter.
 *
 * Creates the pool accounts and both pool roles; the enrolment into courses
 * follows once course categories are configured (scheduled task or first preview).
 *
 * @package   block_lernobjektefilter
 * @copyright 2026 Moodle in Niedersachsen e. V.
 * @author    Moodle in Niedersachsen e. V.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Post-install tasks for block_lernobjektefilter.
 */
function xmldb_block_lernobjektefilter_install(): void {
    \block_lernobjektefilter\pool_manager::ensure_role();
    \block_lernobjektefilter\pool_manager::create_pool_accounts();
}
