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
 * Review page for pending AI keyword suggestions: apply or discard each one.
 *
 * @package   block_lernobjektefilter
 * @copyright 2026 Moodle in Niedersachsen e. V.
 * @author    Moodle in Niedersachsen e. V.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use block_lernobjektefilter\tag_review;

require_login();
$context = context_system::instance();
require_capability('block/lernobjektefilter:reviewtags', $context);

$action = optional_param('action', '', PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);

$url = new moodle_url('/blocks/lernobjektefilter/review.php');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('review_heading', 'block_lernobjektefilter'));
$PAGE->set_heading(get_string('review_heading', 'block_lernobjektefilter'));

if ($action !== '') {
    require_sesskey();
    if ($action === 'apply' && tag_review::apply($id)) {
        redirect($url, get_string('review_applied', 'block_lernobjektefilter'), null, \core\output\notification::NOTIFY_SUCCESS);
    }
    if ($action === 'discard' && tag_review::discard($id)) {
        redirect($url, get_string('review_discarded', 'block_lernobjektefilter'), null, \core\output\notification::NOTIFY_INFO);
    }
    redirect($url);
}

$pending = tag_review::pending();

echo $OUTPUT->header();

if (!$pending) {
    echo $OUTPUT->notification(get_string('review_empty', 'block_lernobjektefilter'), \core\output\notification::NOTIFY_INFO);
} else {
    $table = new html_table();
    $table->head = [
        get_string('review_col_object', 'block_lernobjektefilter'),
        get_string('review_col_course', 'block_lernobjektefilter'),
        get_string('review_col_tags', 'block_lernobjektefilter'),
        get_string('review_col_action', 'block_lernobjektefilter'),
    ];
    foreach ($pending as $suggestion) {
        $modcontext = context_module::instance($suggestion->cmid);
        $objectlink = html_writer::link(
            new moodle_url('/mod/' . $suggestion->modname . '/view.php', ['id' => $suggestion->cmid]),
            format_string($suggestion->name, true, ['context' => $modcontext])
        );
        $applybutton = new single_button(
            new moodle_url($url, ['action' => 'apply', 'id' => $suggestion->id]),
            get_string('btn_apply', 'block_lernobjektefilter'),
            'post',
            single_button::BUTTON_PRIMARY
        );
        $actions = $OUTPUT->render($applybutton) . ' ' . $OUTPUT->single_button(
            new moodle_url($url, ['action' => 'discard', 'id' => $suggestion->id]),
            get_string('btn_discard', 'block_lernobjektefilter'),
            'post'
        );
        $table->data[] = [
            $objectlink,
            format_string($suggestion->coursename, true, ['context' => context_course::instance($suggestion->courseid)]),
            s(implode(', ', tag_review::decode($suggestion->tags))),
            $actions,
        ];
    }
    echo html_writer::table($table);
}

echo $OUTPUT->footer();
