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
 * Admin settings for block_lernobjektefilter.
 *
 * @package   block_lernobjektefilter
 * @copyright 2026 Moodle in Niedersachsen e. V.
 * @author    Moodle in Niedersachsen e. V.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // Scope: course categories and activity types that count as learning objects.
    $settings->add(new admin_setting_heading(
        'block_lernobjektefilter/scope_heading',
        get_string('settings_scope_heading', 'block_lernobjektefilter'),
        get_string('settings_scope_desc', 'block_lernobjektefilter')
    ));
    $settings->add(new admin_setting_configmultiselect(
        'block_lernobjektefilter/categories',
        get_string('settings_categories', 'block_lernobjektefilter'),
        get_string('settings_categories_help', 'block_lernobjektefilter'),
        [],
        core_course_category::make_categories_list('', 0, ' / ')
    ));
    $settings->add(new admin_setting_configcheckbox(
        'block_lernobjektefilter/includesubcategories',
        get_string('settings_includesubcategories', 'block_lernobjektefilter'),
        get_string('settings_includesubcategories_help', 'block_lernobjektefilter'),
        1
    ));
    $settings->add(new admin_setting_configmulticheckbox(
        'block_lernobjektefilter/modtypes',
        get_string('settings_modtypes', 'block_lernobjektefilter'),
        get_string('settings_modtypes_help', 'block_lernobjektefilter'),
        \block_lernobjektefilter\object_scope::default_modtypes(),
        \block_lernobjektefilter\object_scope::modtype_choices()
    ));
    $settings->add(new admin_setting_configtext(
        'block_lernobjektefilter/resultlimit',
        get_string('settings_resultlimit', 'block_lernobjektefilter'),
        get_string('settings_resultlimit_help', 'block_lernobjektefilter'),
        '100',
        PARAM_INT
    ));

    // Keyword lists; every non-empty list becomes a row of filter chips in the block.
    $settings->add(new admin_setting_heading(
        'block_lernobjektefilter/tags_heading',
        get_string('settings_tags_heading', 'block_lernobjektefilter'),
        get_string('settings_tags_desc', 'block_lernobjektefilter')
    ));
    $settings->add(new admin_setting_configtextarea(
        'block_lernobjektefilter/schooltypes',
        get_string('settings_schooltypes', 'block_lernobjektefilter'),
        get_string('settings_schooltypes_help', 'block_lernobjektefilter'),
        "Grundschule\nHauptschule\nRealschule\nOberschule\nGymnasium\nIGS\nKGS\nFörderschule\nBerufsbildende Schule"
    ));
    $settings->add(new admin_setting_configtextarea(
        'block_lernobjektefilter/subjects',
        get_string('settings_subjects', 'block_lernobjektefilter'),
        get_string('settings_subjects_help', 'block_lernobjektefilter'),
        "Mathematik\nDeutsch\nEnglisch\nFranzösisch\nSpanisch\nLatein\nNaturwissenschaften\nBiologie\nChemie\nPhysik\n" .
            "Gesellschaftslehre\nGeschichte\nErdkunde\nPolitik-Wirtschaft\nReligion\nWerte und Normen\nKunst\nMusik\n" .
            "Darstellendes Spiel\nSport\nInformatik\nArbeit-Wirtschaft-Technik\nHauswirtschaft\nTextiles Gestalten"
    ));
    $settings->add(new admin_setting_configtextarea(
        'block_lernobjektefilter/levels',
        get_string('settings_levels', 'block_lernobjektefilter'),
        get_string('settings_levels_help', 'block_lernobjektefilter'),
        "Klasse 1-4\nKlasse 5-6\nKlasse 7-9\nKlasse 10\nOberstufe"
    ));
    $settings->add(new admin_setting_configtextarea(
        'block_lernobjektefilter/materialtypes',
        get_string('settings_materialtypes', 'block_lernobjektefilter'),
        get_string('settings_materialtypes_help', 'block_lernobjektefilter'),
        "Arbeitsblatt\nInteraktive Übung\nErklärvideo\nLernpfad\nSimulation\nTest\nLernspiel\nPräsentation\n" .
            "Infotext\nKopiervorlage\nLösung"
    ));
    $settings->add(new admin_setting_configtext(
        'block_lernobjektefilter/customlabel',
        get_string('settings_customlabel', 'block_lernobjektefilter'),
        get_string('settings_customlabel_help', 'block_lernobjektefilter'),
        '',
        PARAM_TEXT
    ));
    $settings->add(new admin_setting_configtextarea(
        'block_lernobjektefilter/customvalues',
        get_string('settings_customvalues', 'block_lernobjektefilter'),
        get_string('settings_customvalues_help', 'block_lernobjektefilter'),
        ''
    ));

    // Preview through pool accounts.
    $settings->add(new admin_setting_heading(
        'block_lernobjektefilter/pool_heading',
        get_string('settings_pool_heading', 'block_lernobjektefilter'),
        get_string('settings_pool_desc', 'block_lernobjektefilter')
    ));
    $settings->add(new admin_setting_configtext(
        'block_lernobjektefilter/poolsize',
        get_string('settings_poolsize', 'block_lernobjektefilter'),
        get_string('settings_poolsize_help', 'block_lernobjektefilter'),
        '10',
        PARAM_INT
    ));

    // AI tagging through Moodle's AI subsystem.
    $settings->add(new admin_setting_heading(
        'block_lernobjektefilter/ai_heading',
        get_string('settings_ai_heading', 'block_lernobjektefilter'),
        get_string('settings_ai_desc', 'block_lernobjektefilter')
    ));
    $settings->add(new admin_setting_configcheckbox(
        'block_lernobjektefilter/ai_enabled',
        get_string('settings_ai_enabled', 'block_lernobjektefilter'),
        get_string('settings_ai_enabled_help', 'block_lernobjektefilter'),
        0
    ));
    $settings->add(new admin_setting_configcheckbox(
        'block_lernobjektefilter/ai_autoapply',
        get_string('settings_ai_autoapply', 'block_lernobjektefilter'),
        get_string('settings_ai_autoapply_help', 'block_lernobjektefilter'),
        0
    ));
    $settings->add(new admin_setting_configtext(
        'block_lernobjektefilter/ai_batch_size',
        get_string('settings_ai_batch_size', 'block_lernobjektefilter'),
        get_string('settings_ai_batch_size_help', 'block_lernobjektefilter'),
        '20',
        PARAM_INT
    ));

    // AI backend: Ollama (own server) or Anthropic Claude API, as in block_questionfilter.
    $settings->add(new admin_setting_heading(
        'block_lernobjektefilter/ai_backend_heading',
        get_string('settings_ai_backend_heading', 'block_lernobjektefilter'),
        get_string('settings_ai_backend_desc', 'block_lernobjektefilter')
    ));
    $settings->add(new admin_setting_configselect(
        'block_lernobjektefilter/ai_backend',
        get_string('settings_ai_backend', 'block_lernobjektefilter'),
        get_string('settings_ai_backend_help', 'block_lernobjektefilter'),
        'ollama',
        [
            'ollama' => get_string('ai_backend_ollama', 'block_lernobjektefilter'),
            'claude' => get_string('ai_backend_claude', 'block_lernobjektefilter'),
        ]
    ));

    $settings->add(new admin_setting_heading(
        'block_lernobjektefilter/ai_ollama_heading',
        get_string('settings_ai_ollama_heading', 'block_lernobjektefilter'),
        ''
    ));
    $settings->add(new admin_setting_configtext(
        'block_lernobjektefilter/ai_ollama_url',
        get_string('settings_ai_ollama_url', 'block_lernobjektefilter'),
        get_string('settings_ai_ollama_url_help', 'block_lernobjektefilter'),
        \block_lernobjektefilter\ai_connector::OLLAMA_DEFAULT_URL,
        PARAM_URL
    ));
    $settings->add(new admin_setting_configtext(
        'block_lernobjektefilter/ai_ollama_model',
        get_string('settings_ai_ollama_model', 'block_lernobjektefilter'),
        get_string('settings_ai_ollama_model_help', 'block_lernobjektefilter'),
        \block_lernobjektefilter\ai_connector::OLLAMA_DEFAULT_MODEL,
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_heading(
        'block_lernobjektefilter/ai_claude_heading',
        get_string('settings_ai_claude_heading', 'block_lernobjektefilter'),
        get_string('settings_ai_claude_desc', 'block_lernobjektefilter')
    ));
    $settings->add(new admin_setting_configpasswordunmask(
        'block_lernobjektefilter/ai_claude_apikey',
        get_string('settings_ai_claude_apikey', 'block_lernobjektefilter'),
        get_string('settings_ai_claude_apikey_help', 'block_lernobjektefilter'),
        ''
    ));
    $settings->add(new admin_setting_configtext(
        'block_lernobjektefilter/ai_claude_model',
        get_string('settings_ai_claude_model', 'block_lernobjektefilter'),
        get_string('settings_ai_claude_model_help', 'block_lernobjektefilter'),
        \block_lernobjektefilter\ai_connector::CLAUDE_DEFAULT_MODEL,
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_description(
        'block_lernobjektefilter/review_link',
        '',
        html_writer::link(
            new moodle_url('/blocks/lernobjektefilter/review.php'),
            get_string('review_link', 'block_lernobjektefilter')
        )
    ));
}
