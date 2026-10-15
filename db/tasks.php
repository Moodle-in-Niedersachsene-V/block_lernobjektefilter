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
 * Scheduled task definitions for block_lernobjektefilter.
 *
 * @package   block_lernobjektefilter
 * @copyright 2026 Moodle in Niedersachsen e. V.
 * @author    Moodle in Niedersachsen e. V.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = [
    // Create missing pool accounts and enrol them into the courses holding learning objects.
    [
        'classname' => '\block_lernobjektefilter\task\setup_pool',
        'blocking'  => 0,
        'minute'    => '30',
        'hour'      => '3',
        'day'       => '*',
        'month'     => '*',
        'dayofweek' => '*',
        'disabled'  => 0,
    ],
    // AI tagging of learning objects without tag suggestion; disabled until an admin enables it.
    [
        'classname' => '\block_lernobjektefilter\task\tag_objects',
        'blocking'  => 0,
        'minute'    => '30',
        'hour'      => '4',
        'day'       => '*',
        'month'     => '*',
        'dayofweek' => '*',
        'disabled'  => 1,
    ],
];
