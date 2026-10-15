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
 * Tests for the rule "public learning object" (categories, subcategories, types, visibility).
 *
 * @package    block_lernobjektefilter
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(object_scope::class)]
final class object_scope_test extends \advanced_testcase {
    public function test_nothing_configured_matches_nothing(): void {
        $this->resetAfterTest();
        set_config('modtypes', '', 'block_lernobjektefilter');
        $course = $this->getDataGenerator()->create_course();
        $page = $this->getDataGenerator()->create_module('page', ['course' => $course->id]);

        $this->assertFalse(object_scope::is_configured());
        $this->assertNull(object_scope::get_public_object((int)$page->cmid));
        $this->assertSame([], object_scope::course_ids());
    }

    public function test_categories_with_subcategories_and_configured_types_only(): void {
        $this->resetAfterTest();
        $parent = $this->getDataGenerator()->create_category();
        $child = $this->getDataGenerator()->create_category(['parent' => $parent->id]);
        $other = $this->getDataGenerator()->create_category();
        set_config('categories', (string)$parent->id, 'block_lernobjektefilter');
        set_config('includesubcategories', 1, 'block_lernobjektefilter');
        set_config('modtypes', 'page', 'block_lernobjektefilter');

        $incourse = $this->getDataGenerator()->create_course(['category' => $child->id]);
        $outcourse = $this->getDataGenerator()->create_course(['category' => $other->id]);
        $inpage = $this->getDataGenerator()->create_module('page', ['course' => $incourse->id]);
        $inurl = $this->getDataGenerator()->create_module('url', ['course' => $incourse->id]);
        $outpage = $this->getDataGenerator()->create_module('page', ['course' => $outcourse->id]);

        $this->assertTrue(object_scope::is_configured());
        $this->assertNotNull(object_scope::get_public_object((int)$inpage->cmid));
        $this->assertNull(object_scope::get_public_object((int)$inurl->cmid));
        $this->assertNull(object_scope::get_public_object((int)$outpage->cmid));
        $this->assertSame([(int)$incourse->id], object_scope::course_ids());

        set_config('includesubcategories', 0, 'block_lernobjektefilter');
        $this->assertNull(object_scope::get_public_object((int)$inpage->cmid));
    }

    public function test_hidden_courses_and_hidden_modules_are_not_public(): void {
        $this->resetAfterTest();
        $category = $this->getDataGenerator()->create_category();
        set_config('categories', (string)$category->id, 'block_lernobjektefilter');
        set_config('modtypes', 'page', 'block_lernobjektefilter');
        $hiddencourse = $this->getDataGenerator()->create_course(['category' => $category->id, 'visible' => 0]);
        $course = $this->getDataGenerator()->create_course(['category' => $category->id]);
        $inhidden = $this->getDataGenerator()->create_module('page', ['course' => $hiddencourse->id]);
        $hiddenpage = $this->getDataGenerator()->create_module('page', ['course' => $course->id, 'visible' => 0]);
        $page = $this->getDataGenerator()->create_module('page', ['course' => $course->id]);

        $this->assertNull(object_scope::get_public_object((int)$inhidden->cmid));
        $this->assertNull(object_scope::get_public_object((int)$hiddenpage->cmid));
        $object = object_scope::get_public_object((int)$page->cmid);
        $this->assertSame('page', $object->modname);
        $this->assertEquals($course->id, $object->course);
    }

    public function test_unknown_and_disabled_types_are_ignored(): void {
        global $DB;
        $this->resetAfterTest();
        set_config('modtypes', 'page,url,doesnotexist,../user', 'block_lernobjektefilter');
        $DB->set_field('modules', 'visible', 0, ['name' => 'url']);

        $this->assertSame(['page'], object_scope::modtypes());
    }

    public function test_type_choices_contain_only_searchable_activities(): void {
        $this->resetAfterTest();
        $choices = object_scope::modtype_choices();

        $this->assertArrayHasKey('scorm', $choices);
        $this->assertArrayHasKey('h5pactivity', $choices);
        $this->assertArrayHasKey('resource', $choices);
        $this->assertArrayHasKey('scorm', object_scope::default_modtypes());
    }
}
