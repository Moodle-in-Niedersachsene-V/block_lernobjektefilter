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

namespace block_lernobjektefilter\external;

use core_external\external_api;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for the learning object search web service (scope, filters, validation, limits, access).
 *
 * @package    block_lernobjektefilter
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(search_objects::class)]
final class search_objects_test extends \advanced_testcase {
    /** @var \stdClass Configured category. */
    private \stdClass $category;

    /** @var \stdClass Course in the configured category. */
    private \stdClass $course;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->category = $this->getDataGenerator()->create_category();
        $this->course = $this->getDataGenerator()->create_course(['category' => $this->category->id]);
        set_config('categories', (string)$this->category->id, 'block_lernobjektefilter');
        set_config('includesubcategories', 1, 'block_lernobjektefilter');
        set_config('modtypes', 'page,url', 'block_lernobjektefilter');
        set_config('subjects', "Mathematik\nDeutsch", 'block_lernobjektefilter');
        set_config('materialtypes', "Arbeitsblatt\nTest", 'block_lernobjektefilter');
        $this->setUser($this->getDataGenerator()->create_user());
    }

    /**
     * Runs the search the way a web service client does, including return value cleaning.
     *
     * @param array $args Parameters for the search (name => value).
     * @return array Cleaned search result.
     */
    private function search(array $args = []): array {
        $result = search_objects::execute(...$args);
        return external_api::clean_returnvalue(search_objects::execute_returns(), $result);
    }

    /**
     * Returns the course module IDs of a search result.
     *
     * @param array $result Search result.
     * @return int[] Course module IDs.
     */
    private function ids(array $result): array {
        return array_map(fn($o) => $o['cmid'], $result['objects']);
    }

    public function test_only_objects_of_configured_categories_and_types_are_found(): void {
        $other = $this->getDataGenerator()->create_course();
        $page = $this->getDataGenerator()->create_module('page', ['course' => $this->course->id, 'name' => 'Brüche']);
        $url = $this->getDataGenerator()->create_module('url', ['course' => $this->course->id, 'name' => 'Brüche Link']);
        $label = $this->getDataGenerator()->create_module('label', ['course' => $this->course->id]);
        $outside = $this->getDataGenerator()->create_module('page', ['course' => $other->id, 'name' => 'Brüche außen']);

        $result = $this->search(['searchterm' => 'Brüche']);
        $ids = $this->ids($result);

        $this->assertEqualsCanonicalizing([(int)$page->cmid, (int)$url->cmid], $ids);
        $this->assertNotContains((int)$label->cmid, $ids);
        $this->assertNotContains((int)$outside->cmid, $ids);
        $this->assertSame(2, $result['total']);
    }

    public function test_result_carries_type_course_and_url(): void {
        $page = $this->getDataGenerator()->create_module('page', [
            'course' => $this->course->id, 'name' => 'Lyrik', 'intro' => '<p>Gedichte <b>analysieren</b></p>',
        ]);

        $object = $this->search(['modtype' => 'page'])['objects'][0];

        $this->assertSame((int)$page->cmid, $object['cmid']);
        $this->assertSame('page', $object['modname']);
        $this->assertSame(get_string('modulename', 'mod_page'), $object['modtypename']);
        $this->assertStringContainsString('Gedichte', $object['intro']);
        $this->assertStringNotContainsString('<b>', $object['intro']);
        $this->assertStringContainsString('/mod/page/view.php?id=' . $page->cmid, $object['url']);
        $this->assertSame(format_string($this->course->fullname), $object['coursename']);
    }

    public function test_search_term_matches_name_and_description_and_escapes_wildcards(): void {
        $byname = $this->getDataGenerator()->create_module('page', ['course' => $this->course->id, 'name' => 'Physik']);
        $byintro = $this->getDataGenerator()->create_module('page', [
            'course' => $this->course->id, 'name' => 'Versuch', 'intro' => 'Ein Versuch zur PHYSIK',
        ]);

        $found = $this->ids($this->search(['searchterm' => 'physik']));

        $this->assertEqualsCanonicalizing([(int)$byname->cmid, (int)$byintro->cmid], $found);
        $this->assertSame([], $this->ids($this->search(['searchterm' => '%'])));
        $this->assertSame([], $this->ids($this->search(['searchterm' => '_hysik'])));
    }

    public function test_all_keywords_must_match(): void {
        $both = $this->getDataGenerator()->create_module('page', [
            'course' => $this->course->id, 'tags' => ['Mathematik', 'Arbeitsblatt'],
        ]);
        $one = $this->getDataGenerator()->create_module('page', ['course' => $this->course->id, 'tags' => ['Mathematik']]);

        $found = $this->ids($this->search(['tags' => ['Mathematik']]));

        $this->assertEqualsCanonicalizing([(int)$both->cmid, (int)$one->cmid], $found);
        $this->assertSame([(int)$both->cmid], $this->ids($this->search(['tags' => ['mathematik', 'ARBEITSBLATT']])));
    }

    public function test_category_filter_is_limited_to_the_configured_categories(): void {
        $child = $this->getDataGenerator()->create_category(['parent' => $this->category->id]);
        $childcourse = $this->getDataGenerator()->create_course(['category' => $child->id]);
        $inchild = $this->getDataGenerator()->create_module('page', ['course' => $childcourse->id]);
        $inparent = $this->getDataGenerator()->create_module('page', ['course' => $this->course->id]);
        $foreign = $this->getDataGenerator()->create_category();

        $this->assertSame([(int)$inchild->cmid], $this->ids($this->search(['category' => (int)$child->id])));
        $this->assertContains((int)$inparent->cmid, $this->ids($this->search(['category' => (int)$this->category->id])));

        $this->expectException(\invalid_parameter_exception::class);
        $this->search(['category' => (int)$foreign->id]);
    }

    public function test_unconfigured_keyword_is_refused(): void {
        $this->expectException(\invalid_parameter_exception::class);
        $this->search(['tags' => ['Sport']]);
    }

    public function test_unconfigured_type_is_refused(): void {
        $this->expectException(\invalid_parameter_exception::class);
        $this->search(['modtype' => 'forum']);
    }

    public function test_limit_is_capped_by_the_setting(): void {
        for ($i = 1; $i <= 4; $i++) {
            $this->getDataGenerator()->create_module('page', ['course' => $this->course->id, 'name' => "Seite $i"]);
        }
        set_config('resultlimit', 3, 'block_lernobjektefilter');

        $this->assertCount(3, $this->search(['modtype' => 'page'])['objects']);
        $this->assertCount(2, $this->search(['modtype' => 'page', 'limit' => 2])['objects']);
        $names = array_column($this->search(['modtype' => 'page'])['objects'], 'name');
        $this->assertSame(['Seite 1', 'Seite 2', 'Seite 3'], $names);
    }

    public function test_guest_may_search(): void {
        $page = $this->getDataGenerator()->create_module('page', ['course' => $this->course->id]);
        $this->setGuestUser();

        $this->assertSame([(int)$page->cmid], $this->ids($this->search(['modtype' => 'page'])));
    }

    public function test_search_needs_the_capability(): void {
        global $DB;
        $userrole = $DB->get_field('role', 'id', ['shortname' => 'user']);
        assign_capability('block/lernobjektefilter:search', CAP_PROHIBIT, $userrole, \context_system::instance()->id, true);

        $this->expectException(\required_capability_exception::class);
        $this->search(['modtype' => 'page']);
    }

    public function test_rate_limit_refuses_too_many_requests(): void {
        for ($i = 0; $i < search_objects::RATE_LIMIT_REQUESTS; $i++) {
            $this->search(['modtype' => 'page']);
        }

        try {
            $this->search(['modtype' => 'page']);
            $this->fail('The request above the rate limit must be refused.');
        } catch (\moodle_exception $e) {
            $this->assertSame('ratelimitexceeded', $e->errorcode);
        }
    }
}
