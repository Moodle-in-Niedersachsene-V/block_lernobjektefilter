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

/**
 * Hook callbacks: lock pool sessions out of personal pages, independent of theme and roles.
 *
 * The roles of pool_manager already prohibit the actions; this second line hides messaging and
 * sends pool sessions from personal pages back to the site home, so that a visitor never sees
 * profile, preferences, messages, mail or dashboard of a pool account.
 *
 * @package   block_lernobjektefilter
 * @copyright 2026 Moodle in Niedersachsen e. V.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /** Script paths (relative to the web root) that pool sessions may not open; a trailing slash blocks a directory. */
    public const BLOCKED_PATHS = [
        '/user/edit.php',
        '/user/editadvanced.php',
        '/user/preferences.php',
        '/user/profile.php',
        '/user/language.php',
        '/user/forum.php',
        '/user/editor.php',
        '/user/calendar.php',
        '/user/contentbank.php',
        '/user/files.php',
        '/user/managetoken.php',
        '/user/defaulthomepage.php',
        '/login/change_password.php',
        '/message/',
        '/local/mail/',
        '/my/',
        '/calendar/',
        '/grade/report/overview/',
        '/badges/mybadges.php',
        '/blog/',
        '/admin/tool/dataprivacy/',
    ];

    /**
     * Listener for the after_config hook.
     *
     * @param \core\hook\after_config $hook Hook.
     */
    public static function after_config(\core\hook\after_config $hook): void {
        global $CFG, $SCRIPT;

        if (during_initial_install() || isset($CFG->upgraderunning) || !pool_manager::is_pool_session()) {
            return;
        }

        // Pool sessions have no messaging at all: the message drawer and notifications disappear.
        $CFG->messaging = false;

        $noredirect = (defined('CLI_SCRIPT') && CLI_SCRIPT)
            || (defined('AJAX_SCRIPT') && AJAX_SCRIPT)
            || (defined('WS_SERVER') && WS_SERVER);
        if ($noredirect) {
            return;
        }
        if (self::is_blocked((string)($SCRIPT ?? ''))) {
            redirect(new \moodle_url('/'));
        }
    }

    /**
     * Whether a script path is closed for pool sessions.
     *
     * @param string $script Script path relative to the web root, e.g. /user/edit.php.
     * @return bool
     */
    public static function is_blocked(string $script): bool {
        foreach (self::BLOCKED_PATHS as $path) {
            if (str_ends_with($path, '/') ? str_starts_with($script, $path) : $script === $path) {
                return true;
            }
        }
        return false;
    }
}
