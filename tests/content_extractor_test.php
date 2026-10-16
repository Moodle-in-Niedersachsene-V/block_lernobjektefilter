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

use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for the content excerpt sent to the AI (glossary, page, files) and the richer prompt.
 *
 * @package    block_lernobjektefilter
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(content_extractor::class)]
#[CoversClass(tag_suggester::class)]
final class content_extractor_test extends \advanced_testcase {
    /**
     * Learning object record as the task builds it.
     *
     * @param \stdClass $module Module record from the generator (with cmid and id).
     * @param string $modname Module name.
     * @return \stdClass Learning object.
     */
    private function object(\stdClass $module, string $modname): \stdClass {
        return (object)[
            'cmid'     => (int)$module->cmid,
            'instance' => (int)$module->id,
            'modname'  => $modname,
            'name'     => $module->name,
            'intro'    => '',
        ];
    }

    public function test_glossary_terms_and_definitions_are_read_without_authors(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $author = $this->getDataGenerator()->create_user(['firstname' => 'Erika', 'lastname' => 'Musterfrau']);
        $glossary = $this->getDataGenerator()->create_module('glossary', ['course' => $course->id]);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_glossary');
        $generator->create_content($glossary, [
            'concept' => 'Bruch', 'definition' => '<p>Teil eines Ganzen</p>', 'userid' => $author->id,
        ]);
        $generator->create_content($glossary, [
            'concept' => 'Nenner', 'definition' => 'Zahl unter dem Bruchstrich', 'userid' => $author->id,
        ]);

        $text = content_extractor::extract($this->object($glossary, 'glossary'));

        $this->assertStringContainsString('Bruch: Teil eines Ganzen', $text);
        $this->assertStringContainsString('Nenner: Zahl unter dem Bruchstrich', $text);
        $this->assertStringNotContainsString('Musterfrau', $text);
    }

    public function test_page_text_is_read_and_shortened(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $page = $this->getDataGenerator()->create_module('page', [
            'course' => $course->id, 'content' => '<p>Die Photosynthese</p>' . str_repeat(' Chlorophyll', 600),
        ]);

        $text = content_extractor::extract($this->object($page, 'page'));

        $this->assertStringStartsWith('Die Photosynthese', $text);
        $this->assertLessThanOrEqual(content_extractor::MAX_LENGTH, \core_text::strlen($text));
    }

    public function test_file_names_of_a_file_resource_are_read(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $resource = $this->getDataGenerator()->create_module('resource', [
            'course' => $course->id, 'defaultfilename' => 'Arbeitsblatt_Bruchrechnung.pdf',
        ]);

        $text = content_extractor::extract($this->object($resource, 'resource'));

        $this->assertStringContainsString('Arbeitsblatt_Bruchrechnung.pdf', $text);
    }

    public function test_types_without_readable_content_give_an_empty_excerpt(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $label = $this->getDataGenerator()->create_module('label', ['course' => $course->id]);

        $this->assertSame('', content_extractor::extract($this->object($label, 'label')));
    }

    public function test_prompt_carries_content_type_name_and_context(): void {
        $this->resetAfterTest();
        set_config('subjects', "Mathematik\nDeutsch", 'block_lernobjektefilter');
        $course = $this->getDataGenerator()->create_course(['summary' => 'Bruchrechnung für Klasse 6']);
        $glossary = $this->getDataGenerator()->create_module('glossary', ['course' => $course->id]);
        $this->getDataGenerator()->get_plugin_generator('mod_glossary')->create_content($glossary, ['concept' => 'Zähler']);
        $object = $this->object($glossary, 'glossary');
        $object->coursename = $course->fullname;
        $object->coursesummary = $course->summary;
        $object->sectionname = 'Brüche';
        $object->categoryname = 'Mathematik';

        $prompt = tag_suggester::build_prompt($object, filter_groups::lists());

        $this->assertStringContainsString('Zähler', $prompt);
        $this->assertStringContainsString(get_string('modulename', 'mod_glossary'), $prompt);
        $this->assertStringContainsString('Bruchrechnung für Klasse 6', $prompt);
        $this->assertStringContainsString('Brüche', $prompt);
    }
}
