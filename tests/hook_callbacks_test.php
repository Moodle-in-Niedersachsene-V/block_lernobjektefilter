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
 * Tests for the lock-out of pool sessions (blocked pages, messaging off).
 *
 * @package    block_lernobjektefilter
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(hook_callbacks::class)]
final class hook_callbacks_test extends \advanced_testcase {
    public function test_personal_pages_are_blocked_course_pages_not(): void {
        $blocked = [
            '/user/edit.php', '/user/preferences.php', '/user/profile.php', '/message/index.php',
            '/local/mail/view.php', '/my/', '/my/index.php', '/calendar/view.php', '/login/change_password.php',
        ];
        foreach ($blocked as $script) {
            $this->assertTrue(hook_callbacks::is_blocked($script), $script);
        }
        foreach (['/course/view.php', '/mod/page/view.php', '/index.php', '/user/editx.php', '/myself.php'] as $script) {
            $this->assertFalse(hook_callbacks::is_blocked($script), $script);
        }
    }

    public function test_messaging_is_switched_off_for_pool_sessions_only(): void {
        global $CFG, $DB;
        $this->resetAfterTest();
        $CFG->messaging = true;
        $this->setUser($this->getDataGenerator()->create_user());

        hook_callbacks::after_config(new \core\hook\after_config());
        $this->assertTrue((bool)$CFG->messaging);

        pool_manager::create_pool_accounts();
        $this->setUser($DB->get_record('user', ['username' => pool_manager::get_pool_usernames()[0]]));

        hook_callbacks::after_config(new \core\hook\after_config());
        $this->assertFalse((bool)$CFG->messaging);
    }
}
