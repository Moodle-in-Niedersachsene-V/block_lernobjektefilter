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

use block_lernobjektefilter\tag_review;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for the AI tagging task and the review of its suggestions.
 *
 * @package    block_lernobjektefilter
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(tag_objects::class)]
#[CoversClass(tag_review::class)]
final class tag_objects_test extends \advanced_testcase {
    /** @var \GuzzleHttp\Handler\MockHandler Queue of provider HTTP answers. */
    private $mock;

    /** @var \stdClass Course in the configured category. */
    private \stdClass $course;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $category = $this->getDataGenerator()->create_category();
        $this->course = $this->getDataGenerator()->create_course(['category' => $category->id]);
        set_config('categories', (string)$category->id, 'block_lernobjektefilter');
        set_config('modtypes', 'page', 'block_lernobjektefilter');
        set_config('ai_enabled', 1, 'block_lernobjektefilter');
        set_config('subjects', "Mathematik\nDeutsch", 'block_lernobjektefilter');
        \core\di::get(\core_ai\manager::class)->create_provider_instance(
            classname: '\aiprovider_ollama\provider',
            name: 'test',
            enabled: true,
            config: ['endpoint' => 'http://ollama.example:11434/'],
            actionconfig: [\core_ai\aiactions\generate_text::class => ['enabled' => true, 'settings' => [
                'model' => 'test',
                'systeminstruction' => get_string('action_generate_text_instruction', 'core_ai'),
            ]]],
        );
        ['mock' => $this->mock] = $this->get_mocked_http_client();
    }

    /**
     * Queue the model answer the provider returns next.
     *
     * @param string $answer Generated text.
     */
    private function answer(string $answer): void {
        $this->mock->append(new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'model' => 'test', 'response' => $answer, 'done' => true, 'done_reason' => 'stop',
            'prompt_eval_count' => 1, 'eval_count' => 1,
        ])));
    }

    /**
     * Tags of a learning object.
     *
     * @param int $cmid Course module ID.
     * @return string[] Tag names.
     */
    private function tags(int $cmid): array {
        return array_values(\core_tag_tag::get_item_tags_array('core', 'course_modules', $cmid));
    }

    /**
     * Run the task and swallow its log output.
     */
    private function run_task(): void {
        ob_start();
        try {
            (new tag_objects())->execute();
        } finally {
            ob_end_clean();
        }
    }

    public function test_disabled_setting_does_nothing(): void {
        global $DB;
        set_config('ai_enabled', 0, 'block_lernobjektefilter');
        $this->getDataGenerator()->create_module('page', ['course' => $this->course->id]);

        $this->run_task();

        $this->assertSame(0, $DB->count_records(tag_review::TABLE));
    }

    public function test_autoapply_adds_tags_and_each_object_is_asked_once(): void {
        global $DB;
        set_config('ai_autoapply', 1, 'block_lernobjektefilter');
        $page = $this->getDataGenerator()->create_module('page', ['course' => $this->course->id]);
        $this->answer('{"subjects": ["Mathematik"]}');

        $this->run_task();
        $this->run_task();

        $this->assertSame(['Mathematik'], $this->tags((int)$page->cmid));
        $this->assertSame('applied', $DB->get_field(tag_review::TABLE, 'status', ['cmid' => $page->cmid]));
    }

    public function test_pending_suggestion_is_applied_or_discarded_in_the_review(): void {
        global $DB;
        $first = $this->getDataGenerator()->create_module('page', ['course' => $this->course->id]);
        $second = $this->getDataGenerator()->create_module('page', ['course' => $this->course->id]);
        $this->answer('{"subjects": ["Deutsch"]}');
        $this->answer('{"subjects": ["Mathematik"]}');

        $this->run_task();
        $pending = tag_review::pending();
        $this->assertCount(2, $pending);
        $this->assertSame([], $this->tags((int)$first->cmid));

        $ids = $DB->get_records_menu(tag_review::TABLE, null, '', 'cmid, id');
        $this->assertTrue(tag_review::apply((int)$ids[$first->cmid]));
        $this->assertTrue(tag_review::discard((int)$ids[$second->cmid]));
        $this->assertFalse(tag_review::apply((int)$ids[$second->cmid]));

        $this->assertSame(['Deutsch'], $this->tags((int)$first->cmid));
        $this->assertSame([], $this->tags((int)$second->cmid));
        $this->assertSame([], tag_review::pending());
    }

    public function test_keywords_no_longer_configured_are_not_applied(): void {
        global $DB;
        $page = $this->getDataGenerator()->create_module('page', ['course' => $this->course->id]);
        $this->answer('{"subjects": ["Deutsch"]}');
        $this->run_task();
        set_config('subjects', 'Mathematik', 'block_lernobjektefilter');

        $id = (int)$DB->get_field(tag_review::TABLE, 'id', ['cmid' => $page->cmid]);
        $this->assertFalse(tag_review::apply($id));
        $this->assertSame([], $this->tags((int)$page->cmid));
        $this->assertSame('empty', $DB->get_field(tag_review::TABLE, 'status', ['id' => $id]));
    }

    public function test_failed_request_leaves_no_record_and_fails_the_task(): void {
        global $DB;
        $this->getDataGenerator()->create_module('page', ['course' => $this->course->id]);
        $this->mock->append(new Response(500, [], 'down'));

        try {
            $this->run_task();
            $this->fail('A failed learning object must fail the task.');
        } catch (\moodle_exception $e) {
            $this->assertSame('error_ai_tagging_failed', $e->errorcode);
        }
        $this->assertSame(0, $DB->count_records(tag_review::TABLE));
    }

    public function test_deleted_learning_object_loses_its_suggestion(): void {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/course/lib.php');
        $page = $this->getDataGenerator()->create_module('page', ['course' => $this->course->id]);
        $this->answer('{"subjects": ["Deutsch"]}');
        $this->run_task();

        course_delete_module((int)$page->cmid);

        $this->assertFalse($DB->record_exists(tag_review::TABLE, ['cmid' => $page->cmid]));
    }
}
