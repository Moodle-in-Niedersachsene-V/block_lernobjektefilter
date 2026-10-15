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
 * English language strings for block_lernobjektefilter.
 *
 * @package   block_lernobjektefilter
 * @copyright 2026 Moodle in Niedersachsen e. V.
 * @author    Moodle in Niedersachsen e. V.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['ai_backend_claude']                     = 'Anthropic Claude API';
$string['ai_backend_ollama']                     = 'Ollama (own server)';
$string['back_to_search']                        = 'Back to the search';
$string['btn_apply']                             = 'Apply';
$string['btn_discard']                           = 'Discard';
$string['btn_preview']                           = 'Preview';
$string['btn_reset']                             = 'Reset';
$string['error_ai_answer_invalid']               = 'The AI answer is not the expected JSON: {$a}';
$string['error_ai_request_failed']               = 'The AI request failed: {$a}';
$string['error_ai_tagging_failed']               = 'AI tagging failed for {$a->failed} of {$a->total} learning objects; the causes are in the task log.';
$string['error_ai_unavailable']                  = 'AI tagging is enabled, but the AI backend is not configured: enter the Claude API key or the Ollama URL in the block settings.';
$string['error_invalidcategory']                 = 'This course category is not part of the learning object search.';
$string['error_invalidmodtype']                  = 'This activity type is not part of the learning object search.';
$string['error_invalidtag']                      = 'At least one keyword is not in the configured keyword lists.';
$string['error_preview_needs_post']              = 'A preview can only be started with the preview button of the learning object filter.';
$string['filter_all']                            = '– All –';
$string['hint_notconfigured']                    = 'The learning object search is not configured yet: choose course categories and activity types in the block settings.';
$string['hint_setfilter']                        = 'Set a filter to search learning objects.';
$string['label_category']                        = 'Course category';
$string['label_category_help']                   = 'Search only in this course category and its subcategories. The list shows only the course categories released for the learning object search.';
$string['label_custom']                          = 'Further keywords';
$string['label_custom_help']                     = 'Keywords of the additional list configured by the administration. A learning object matches if it carries a tag with exactly this value.';
$string['label_level']                           = 'Level';
$string['label_level_help']                      = 'Year or level the learning object is meant for. A learning object matches if it carries a tag with exactly this value.';
$string['label_materialtype']                    = 'Material type';
$string['label_materialtype_help']               = 'Kind of material, e.g. worksheet, interactive exercise or explanatory video. A learning object matches if it carries a tag with exactly this value.';
$string['label_modtype']                         = 'Format';
$string['label_modtype_help']                    = 'Moodle activity type of the learning object, e.g. SCORM package, H5P or file. Only the types released for the learning object search are shown.';
$string['label_schooltype']                      = 'School type';
$string['label_schooltype_help']                 = 'School type the learning object is meant for. A learning object matches if it carries a tag with exactly this value.';
$string['label_searchterm']                      = 'Search term';
$string['label_searchterm_help']                 = 'Free text that is searched in the name and the description of the learning objects; upper and lower case do not matter.';
$string['label_subject']                         = 'Subject';
$string['label_subject_help']                    = 'School subject of the learning object. A learning object matches if it carries a tag with exactly this value.';
$string['lernobjektefilter:addinstance']         = 'Add a new learning object filter block';
$string['lernobjektefilter:myaddinstance']       = 'Add a new learning object filter block to the Dashboard';
$string['lernobjektefilter:reviewtags']          = 'Review AI keyword suggestions for learning objects';
$string['lernobjektefilter:search']              = 'Search learning objects with the learning object filter';
$string['object_not_found']                      = 'This learning object was not found or is not publicly accessible.';
$string['placeholder_searchterm']                = 'Search name or description …';
$string['pluginname']                            = 'Learning object filter';
$string['pool_account_description']              = 'Pool account of the learning object filter block for previews.';
$string['pool_account_firstname']                = 'Pool account';
$string['pool_full']                             = 'All pool accounts are in use at the moment. Please try again in a few minutes.';
$string['preview_title']                         = 'View the learning object like a non-editing teacher';
$string['privacy:metadata']                      = 'The learning object filter stores keyword suggestions per learning object, but no personal data of users. For AI tagging it sends content of learning objects (name, type, course, category, description), no personal data, to the configured AI backend (Ollama server or Anthropic Claude API).';
$string['ratelimitexceeded']                     = 'Too many search requests. Please wait {$a->seconds} seconds.';
$string['results_count']                         = 'Learning objects found: {$a}';
$string['results_none']                          = 'No learning objects found.';
$string['review_applied']                        = 'The keywords were added as tags.';
$string['review_col_action']                     = 'Action';
$string['review_col_course']                     = 'Course';
$string['review_col_object']                     = 'Learning object';
$string['review_col_tags']                       = 'Suggested keywords';
$string['review_discarded']                      = 'The suggestion was discarded.';
$string['review_empty']                          = 'There are no keyword suggestions waiting for review.';
$string['review_heading']                        = 'Review keyword suggestions for learning objects';
$string['review_link']                           = 'Review pending keyword suggestions';
$string['role_pool_description']                 = 'Course role of the pool accounts of the learning object filter: like a non-editing teacher, but without participant list, user identity, grades and hidden content, and without posting, commenting or rating.';
$string['role_pool_name']                        = 'Learning object filter pool role';
$string['role_pooluser_description']             = 'System role of the pool accounts of the learning object filter: prohibits changing profile, password, messaging settings, private files, blog and dashboard, and sending messages.';
$string['role_pooluser_name']                    = 'Learning object filter pool account';
$string['settings_ai_autoapply']                 = 'Apply keywords automatically';
$string['settings_ai_autoapply_help']            = 'If enabled, matching values become tags of the learning object at once. If disabled, they wait as suggestions on the review page.';
$string['settings_ai_backend']                   = 'AI backend';
$string['settings_ai_backend_desc']              = 'Choose whether keyword suggestions come from an Ollama server or from the Anthropic Claude API. The plugin calls the backend directly (as the question filter does); Moodle\'s AI subsystem is not used. Requests run through Moodle\'s curl with the site\'s security settings: an Ollama server on localhost or an internal network address, or on port 11434, must be allowed under Site administration > General > Security > HTTP security.';
$string['settings_ai_backend_heading']           = 'AI backend';
$string['settings_ai_backend_help']              = 'Ollama runs on an own server. The Claude API requires an Anthropic API key.';
$string['settings_ai_batch_size']                = 'Learning objects per run';
$string['settings_ai_batch_size_help']           = 'Number of learning objects tagged per run of the scheduled task (default: 20, max. 200).';
$string['settings_ai_claude_apikey']             = 'Claude API key';
$string['settings_ai_claude_apikey_help']        = 'Your Anthropic API key (starts with "sk-ant-"). It is shown masked and used only for the AI requests of this plugin.';
$string['settings_ai_claude_desc']               = 'API key and model for the Anthropic Claude API. Only content of learning objects is sent, no personal data.';
$string['settings_ai_claude_heading']            = 'Claude API settings';
$string['settings_ai_claude_model']              = 'Claude model';
$string['settings_ai_claude_model_help']         = 'Model name, e.g. claude-haiku-4-5-20251001. Haiku is fast and inexpensive for tagging.';
$string['settings_ai_desc']                      = 'The scheduled task asks the configured AI backend (Ollama or Claude API) once per learning object which keywords fit. Only name, activity type, course name, course category and the beginning of the description are sent. Only values from the keyword lists are kept.';
$string['settings_ai_enabled']                   = 'Enable AI tagging';
$string['settings_ai_enabled_help']              = 'Allows the scheduled task "AI tagging of learning objects" to run; the task must also be enabled in the scheduled tasks.';
$string['settings_ai_heading']                   = 'AI tagging';
$string['settings_ai_ollama_heading']            = 'Ollama settings';
$string['settings_ai_ollama_model']              = 'Ollama model';
$string['settings_ai_ollama_model_help']         = 'Name of the Ollama model, e.g. gemma3:4b or llama3.2. The model must be available on the Ollama server.';
$string['settings_ai_ollama_url']                = 'Ollama URL';
$string['settings_ai_ollama_url_help']           = 'Base URL of the Ollama server, e.g. http://localhost:11434, without trailing slash.';
$string['settings_categories']                   = 'Course categories';
$string['settings_categories_help']              = 'Learning objects are searched only in these course categories. Without a selection the search finds nothing.';
$string['settings_customlabel']                  = 'Name of the additional keyword list';
$string['settings_customlabel_help']             = 'Heading of the additional keyword list in the block, e.g. Topic or Competence.';
$string['settings_customvalues']                 = 'Additional keyword list';
$string['settings_customvalues_help']            = 'One keyword per line. Leave empty to hide the list.';
$string['settings_includesubcategories']         = 'Include subcategories';
$string['settings_includesubcategories_help']    = 'If enabled, the subcategories of the selected course categories are searched as well.';
$string['settings_levels']                       = 'Levels';
$string['settings_levels_help']                  = 'One level per line, e.g. Klasse 5-6. Leave empty to hide the list.';
$string['settings_materialtypes']                = 'Material types';
$string['settings_materialtypes_help']           = 'One material type per line, e.g. Arbeitsblatt. Leave empty to hide the list.';
$string['settings_modtypes']                     = 'Activity types';
$string['settings_modtypes_help']                = 'Course activities of these types count as learning objects, e.g. SCORM package, H5P, file or page.';
$string['settings_pool_desc']                    = 'Visitors view learning objects in the preview through a pool account. Missing pool accounts are created and enrolled by the scheduled task (daily at 03:30).';
$string['settings_pool_heading']                 = 'Pool accounts';
$string['settings_poolsize']                     = 'Number of pool accounts';
$string['settings_poolsize_help']                = 'Number of pool accounts and thus of simultaneous previews (default: 10, max. 50).';
$string['settings_resultlimit']                  = 'Max. results';
$string['settings_resultlimit_help']             = 'Maximum number of learning objects per search request (default: 100, max. 200).';
$string['settings_schooltypes']                  = 'School types';
$string['settings_schooltypes_help']             = 'One school type per line, e.g. Gymnasium. Leave empty to hide the list.';
$string['settings_scope_desc']                   = 'Which course activities the block finds: activities of the selected types in visible courses of the selected course categories.';
$string['settings_scope_heading']                = 'Search scope';
$string['settings_subjects']                     = 'Subjects';
$string['settings_subjects_help']                = 'One subject per line, e.g. Mathematik. Leave empty to hide the list.';
$string['settings_tags_desc']                    = 'Each non-empty list appears as a row of chips in the block. A learning object matches a value if it carries a tag with exactly this value (activity tags); upper and lower case do not matter.';
$string['settings_tags_heading']                 = 'Keywords';
$string['task_setup_pool']                       = 'Create learning object filter pool accounts and enrol them into the courses with learning objects';
$string['task_tag_objects']                      = 'AI tagging of learning objects for the learning object filter';
