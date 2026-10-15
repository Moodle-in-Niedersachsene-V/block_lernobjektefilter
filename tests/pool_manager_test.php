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
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests for the pool accounts (creation, roles, enrolment, occupancy).
 *
 * @package    block_lernobjektefilter
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(pool_manager::class)]
#[CoversClass(observer::class)]
final class pool_manager_test extends \advanced_testcase {
    /**
     * The first pool account, created if missing.
     *
     * @return \stdClass User record.
     */
    private function first_pool_user(): \stdClass {
        global $DB;
        pool_manager::create_pool_accounts();
        return $DB->get_record('user', ['username' => pool_manager::get_pool_usernames()[0], 'deleted' => 0], '*', MUST_EXIST);
    }

    public function test_pool_size_uses_default_and_is_capped(): void {
        $this->resetAfterTest();

        $this->assertSame(pool_manager::POOL_SIZE_DEFAULT, pool_manager::get_pool_size());
        set_config('poolsize', -3, 'block_lernobjektefilter');
        $this->assertSame(pool_manager::POOL_SIZE_DEFAULT, pool_manager::get_pool_size());
        set_config('poolsize', 100000, 'block_lernobjektefilter');
        $this->assertSame(pool_manager::POOL_SIZE_MAX, pool_manager::get_pool_size());
        $this->assertCount(pool_manager::POOL_SIZE_MAX, pool_manager::get_pool_usernames());
    }

    public function test_create_pool_accounts_creates_missing_accounts_only_once(): void {
        global $DB;
        $this->resetAfterTest();
        set_config('poolsize', 12, 'block_lernobjektefilter');
        [$insql, $params] = $DB->get_in_or_equal(pool_manager::get_pool_usernames());
        $existing = $DB->count_records_select('user', "username $insql AND deleted = 0", $params);

        $first = pool_manager::create_pool_accounts();
        $second = pool_manager::create_pool_accounts();

        $this->assertDebuggingNotCalled();
        $this->assertSame(12 - $existing, $first);
        $this->assertSame(0, $second);
        $this->assertSame(12, $DB->count_records_select('user', "username $insql AND deleted = 0", $params));
    }

    public function test_pool_accounts_cannot_log_in_with_guessable_passwords(): void {
        $this->resetAfterTest();
        $user = $this->first_pool_user();
        $_SERVER['HTTP_USER_AGENT'] = 'phpunit';

        foreach (['', $user->username, 'password', 'lernobjekte', 'Pool-Konto'] as $password) {
            $this->assertFalse(authenticate_user_login($user->username, $password), "Password '$password'");
        }
    }

    /**
     * Capabilities the system role of pool accounts must prohibit.
     *
     * @return array[]
     */
    public static function system_capabilities(): array {
        return array_map(fn($cap) => [$cap], array_combine(
            pool_manager::SYSTEM_PROHIBITED_CAPABILITIES,
            pool_manager::SYSTEM_PROHIBITED_CAPABILITIES
        ));
    }

    /**
     * Pool accounts may not change their profile, password and the like, so that nobody can take one over.
     *
     * @param string $capability Capability.
     */
    #[DataProvider('system_capabilities')]
    public function test_system_role_prohibits_account_changes(string $capability): void {
        $this->resetAfterTest();
        $user = $this->first_pool_user();

        $this->assertFalse(has_capability($capability, \context_system::instance(), $user));
        $this->assertFalse(has_capability($capability, \context_user::instance($user->id), $user));
    }

    /**
     * Capabilities the course role of pool accounts must prohibit.
     *
     * @return array[]
     */
    public static function course_capabilities(): array {
        return array_map(fn($cap) => [$cap], array_combine(
            pool_manager::PROHIBITED_CAPABILITIES,
            pool_manager::PROHIBITED_CAPABILITIES
        ));
    }

    /**
     * Pool accounts see a course, but no personal data, no hidden content and leave no traces.
     *
     * @param string $capability Capability.
     */
    #[DataProvider('course_capabilities')]
    public function test_course_role_prohibits_personal_data_and_traces(string $capability): void {
        $this->resetAfterTest();
        $user = $this->first_pool_user();
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', ['course' => $course->id]);

        pool_manager::enrol_pool_into_course((int)$course->id);

        $context = str_starts_with($capability, 'mod/')
            ? \context_module::instance($forum->cmid)
            : \context_course::instance($course->id);
        $this->assertTrue(is_enrolled(\context_course::instance($course->id), $user->id));
        $this->assertFalse(has_capability($capability, $context, $user));
    }

    public function test_enrolment_is_idempotent_and_uses_the_course_role(): void {
        global $DB;
        $this->resetAfterTest();
        set_config('poolsize', 3, 'block_lernobjektefilter');
        pool_manager::create_pool_accounts();
        $course = $this->getDataGenerator()->create_course();

        $this->assertSame(3, pool_manager::enrol_pool_into_course((int)$course->id));
        $this->assertSame(0, pool_manager::enrol_pool_into_course((int)$course->id));

        $roleid = $DB->get_field('role', 'id', ['shortname' => pool_manager::ROLE_SHORTNAME], MUST_EXIST);
        $context = \context_course::instance($course->id);
        $this->assertSame(3, $DB->count_records('role_assignments', ['roleid' => $roleid, 'contextid' => $context->id]));
    }

    public function test_scope_courses_are_enrolled_others_not(): void {
        $this->resetAfterTest();
        set_config('poolsize', 2, 'block_lernobjektefilter');
        $user = $this->first_pool_user();
        $category = $this->getDataGenerator()->create_category();
        set_config('categories', (string)$category->id, 'block_lernobjektefilter');
        set_config('modtypes', 'page', 'block_lernobjektefilter');
        $incourse = $this->getDataGenerator()->create_course(['category' => $category->id]);
        $this->getDataGenerator()->create_module('page', ['course' => $incourse->id]);
        $outcourse = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->create_module('page', ['course' => $outcourse->id]);

        pool_manager::enrol_pool_into_scope_courses();

        $this->assertTrue(is_enrolled(\context_course::instance($incourse->id), $user->id));
        $this->assertFalse(is_enrolled(\context_course::instance($outcourse->id), $user->id));
    }

    public function test_occupied_accounts_are_skipped_and_freed_on_logout(): void {
        $this->resetAfterTest();
        set_config('poolsize', 2, 'block_lernobjektefilter');
        pool_manager::create_pool_accounts();
        $course = $this->getDataGenerator()->create_course();

        $first = pool_manager::occupy_free_account((int)$course->id);
        $second = pool_manager::occupy_free_account((int)$course->id);
        $this->assertNotSame($first->username, $second->username);
        $this->assertNull(pool_manager::occupy_free_account((int)$course->id));

        $event = \core\event\user_loggedout::create([
            'userid'   => $first->id,
            'objectid' => $first->id,
            'other'    => ['sessionid' => 'x'],
        ]);
        observer::user_loggedout($event);

        $this->assertSame($first->username, pool_manager::get_free_pool_account()->username);
    }

    public function test_logout_of_other_users_does_not_free_pool_accounts(): void {
        $this->resetAfterTest();
        set_config('poolsize', 1, 'block_lernobjektefilter');
        pool_manager::create_pool_accounts();
        $course = $this->getDataGenerator()->create_course();
        pool_manager::occupy_free_account((int)$course->id);
        $other = $this->getDataGenerator()->create_user();

        observer::user_loggedout(\core\event\user_loggedout::create([
            'userid'   => $other->id,
            'objectid' => $other->id,
            'other'    => ['sessionid' => 'y'],
        ]));

        $this->assertNull(pool_manager::get_free_pool_account());
    }

    public function test_remove_roles_deletes_both_roles(): void {
        global $DB;
        $this->resetAfterTest();
        pool_manager::ensure_role();

        pool_manager::remove_roles();

        $this->assertFalse($DB->record_exists('role', ['shortname' => pool_manager::ROLE_SHORTNAME]));
        $this->assertFalse($DB->record_exists('role', ['shortname' => pool_manager::SYSTEM_ROLE_SHORTNAME]));
    }
}
