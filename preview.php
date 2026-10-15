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
 * Preview endpoint for block_lernobjektefilter.
 *
 * Hands a free pool account to the visitor, logs it in and redirects to the learning object.
 * Only a POST with a valid sesskey starts a preview, and only for a public learning object
 * (configured category and activity type, visible course and course module).
 *
 * Usage: POST /blocks/lernobjektefilter/preview.php, body cmid=42&sesskey=...
 *
 * @package   block_lernobjektefilter
 * @copyright 2026 Moodle in Niedersachsen e. V.
 * @author    Moodle in Niedersachsen e. V.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:ignore moodle.Files.RequireLogin.Missing,moodle.Commenting.InlineComment.NotCapital,moodle.Commenting.InlineComment.InvalidEndChar -- Public endpoint: it is the login step itself, via a pool account. The Semgrep marker has a fixed syntax.
require_once(__DIR__ . '/../../config.php'); // nosemgrep: moodle-einstiegsdatei-ohne-login

$cmid = required_param('cmid', PARAM_INT);
if (!data_submitted()) {
    throw new moodle_exception('error_preview_needs_post', 'block_lernobjektefilter');
}
require_sesskey();

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/blocks/lernobjektefilter/preview.php'));

$object = \block_lernobjektefilter\object_scope::get_public_object($cmid);
if (!$object) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(
        get_string('object_not_found', 'block_lernobjektefilter'),
        \core\output\notification::NOTIFY_ERROR
    );
    echo $OUTPUT->footer();
    exit;
}

// The module name comes from the modules table (object_scope), so the path is a valid activity path.
$objecturl = new moodle_url('/mod/' . $object->modname . '/view.php', ['id' => $object->id]);

// A real login is never replaced by a pool account; a running preview continues with its pool account.
if (isloggedin() && !isguestuser()) {
    if (\block_lernobjektefilter\pool_manager::is_pool_username($USER->username)) {
        \block_lernobjektefilter\pool_manager::enrol_pool_into_course((int)$object->course);
    }
    redirect($objecturl);
}

$account = \block_lernobjektefilter\pool_manager::occupy_free_account((int)$object->course);
if (!$account) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(
        get_string('pool_full', 'block_lernobjektefilter'),
        \core\output\notification::NOTIFY_WARNING
    );
    echo html_writer::div(html_writer::link(
        new moodle_url('/'),
        get_string('back_to_search', 'block_lernobjektefilter'),
        ['class' => 'btn btn-secondary mt-3']
    ));
    echo $OUTPUT->footer();
    exit;
}

complete_user_login(get_complete_user_data('id', $account->id));
redirect($objecturl);
