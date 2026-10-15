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
 * Pool accounts for the preview of learning objects, their two roles and their occupancy.
 *
 * Same mechanism as in the other search blocks of the materials hub (Kursfilter, Fragenfilter):
 * a visitor gets one free pool account for the time of the preview.
 *
 * - The course role (archetype non-editing teacher) lets the account see the course of the
 *   learning object, but neither participants, user identity, grades nor hidden content,
 *   and it may not post in forums, comment or rate.
 * - The system role prohibits changing profile, password, messaging, private files, blogs and
 *   dashboard, so that no visitor can take over a pool account or leave content for the next one.
 *
 * @package   block_lernobjektefilter
 * @copyright 2026 Moodle in Niedersachsen e. V.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pool_manager {
    /** Default number of pool accounts (documented in the setting). */
    public const POOL_SIZE_DEFAULT = 10;

    /** Maximum allowed pool size (hard cap). */
    public const POOL_SIZE_MAX = 50;

    /** Username prefix for pool accounts. */
    public const USERNAME_PREFIX = 'lernobjekte_pool';

    /** Shortname of the course role of pool accounts. */
    public const ROLE_SHORTNAME = 'lernobjektefilter_pool';

    /** Shortname of the system role of pool accounts. */
    public const SYSTEM_ROLE_SHORTNAME = 'lernobjektefilter_pooluser';

    /** Capabilities prohibited for the course role: personal data, hidden content, visible traces. */
    public const PROHIBITED_CAPABILITIES = [
        'moodle/course:viewparticipants',
        'moodle/site:viewuseridentity',
        'moodle/grade:viewall',
        'moodle/course:viewhiddenactivities',
        'moodle/course:viewhiddensections',
        'mod/forum:startdiscussion',
        'mod/forum:replypost',
        'moodle/comment:post',
        'moodle/rating:rate',
    ];

    /** Capabilities prohibited for the system role: account takeover and content left for the next visitor. */
    public const SYSTEM_PROHIBITED_CAPABILITIES = [
        'moodle/user:editownprofile',
        'moodle/user:changeownpassword',
        'moodle/user:editownmessageprofile',
        'moodle/user:manageownfiles',
        'moodle/site:sendmessage',
        'moodle/blog:create',
        'moodle/my:manageblocks',
    ];

    /** Cache key prefix marking a pool account as occupied. */
    public const CACHE_PREFIX = 'pool_active_';

    /**
     * Return the configured pool size (from admin settings, capped at POOL_SIZE_MAX).
     *
     * @return int
     */
    public static function get_pool_size(): int {
        $configured = (int)get_config('block_lernobjektefilter', 'poolsize');
        if ($configured < 1) {
            $configured = self::POOL_SIZE_DEFAULT;
        }
        return min($configured, self::POOL_SIZE_MAX);
    }

    /**
     * Return the usernames of the pool accounts.
     *
     * @param int|null $size Number of accounts, the configured pool size if null.
     * @return string[]
     */
    public static function get_pool_usernames(?int $size = null): array {
        $names = [];
        for ($i = 1; $i <= ($size ?? self::get_pool_size()); $i++) {
            $names[] = self::USERNAME_PREFIX . str_pad((string)$i, 2, '0', STR_PAD_LEFT);
        }
        return $names;
    }

    /**
     * Whether a username belongs to a pool account of this plugin (up to the maximum pool size).
     *
     * @param string $username Username.
     * @return bool
     */
    public static function is_pool_username(string $username): bool {
        return in_array($username, self::get_pool_usernames(self::POOL_SIZE_MAX), true);
    }

    /**
     * Create the missing pool accounts and give all of them the system role; existing ones stay unchanged.
     *
     * @return int Number of newly created accounts.
     */
    public static function create_pool_accounts(): int {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/user/lib.php');

        $created = 0;
        foreach (self::get_pool_usernames() as $username) {
            if ($DB->record_exists('user', ['username' => $username, 'deleted' => 0])) {
                continue;
            }

            $user               = new \stdClass();
            $user->auth         = 'manual';
            $user->confirmed    = 1;
            $user->mnethostid   = $CFG->mnet_localhost_id;
            $user->username     = $username;
            $user->password     = hash_internal_user_password(self::generate_password());
            $user->firstname    = get_string('pool_account_firstname', 'block_lernobjektefilter');
            $user->lastname     = substr($username, strlen(self::USERNAME_PREFIX));
            $user->email        = $username . '@lernobjektefilter.invalid';
            $user->emailstop    = 1;
            $user->lang         = get_string_manager()->translation_exists('de') ? 'de' : $CFG->lang;
            $user->timecreated  = time();
            $user->timemodified = time();
            $user->description  = get_string('pool_account_description', 'block_lernobjektefilter');

            user_create_user($user, false, false);
            $created++;
        }
        self::assign_system_role();
        return $created;
    }

    /**
     * Create both pool roles if missing.
     *
     * @return int ID of the course role.
     */
    public static function ensure_role(): int {
        global $DB;

        self::ensure_system_role();

        $roleid = $DB->get_field('role', 'id', ['shortname' => self::ROLE_SHORTNAME]);
        if ($roleid) {
            return (int)$roleid;
        }
        $roleid = create_role(
            get_string('role_pool_name', 'block_lernobjektefilter'),
            self::ROLE_SHORTNAME,
            get_string('role_pool_description', 'block_lernobjektefilter'),
            'teacher'
        );
        set_role_contextlevels($roleid, get_default_contextlevels('teacher'));
        // Take over the default capabilities of the archetype (create_role() does not).
        reset_role_capabilities($roleid);
        $systemcontextid = \context_system::instance()->id;
        foreach (self::PROHIBITED_CAPABILITIES as $capability) {
            assign_capability($capability, CAP_PROHIBIT, $roleid, $systemcontextid, true);
        }
        return (int)$roleid;
    }

    /**
     * Create the system role of pool accounts if missing.
     *
     * @return int Role ID.
     */
    public static function ensure_system_role(): int {
        global $DB;

        $roleid = $DB->get_field('role', 'id', ['shortname' => self::SYSTEM_ROLE_SHORTNAME]);
        if ($roleid) {
            return (int)$roleid;
        }
        $roleid = create_role(
            get_string('role_pooluser_name', 'block_lernobjektefilter'),
            self::SYSTEM_ROLE_SHORTNAME,
            get_string('role_pooluser_description', 'block_lernobjektefilter'),
            ''
        );
        set_role_contextlevels($roleid, [CONTEXT_SYSTEM]);
        $systemcontextid = \context_system::instance()->id;
        foreach (self::SYSTEM_PROHIBITED_CAPABILITIES as $capability) {
            assign_capability($capability, CAP_PROHIBIT, $roleid, $systemcontextid, true);
        }
        return (int)$roleid;
    }

    /**
     * Give every existing pool account the system role (idempotent).
     */
    public static function assign_system_role(): void {
        global $DB;

        $roleid = self::ensure_system_role();
        $systemcontextid = \context_system::instance()->id;
        foreach ($DB->get_records_list('user', 'username', self::get_pool_usernames(), '', 'id, deleted') as $user) {
            if (!$user->deleted) {
                role_assign($roleid, $user->id, $systemcontextid);
            }
        }
    }

    /**
     * Delete both pool roles together with their assignments.
     */
    public static function remove_roles(): void {
        global $DB;

        foreach ([self::ROLE_SHORTNAME, self::SYSTEM_ROLE_SHORTNAME] as $shortname) {
            $roleid = $DB->get_field('role', 'id', ['shortname' => $shortname]);
            if ($roleid) {
                delete_role($roleid);
            }
        }
    }

    /**
     * Enrol all pool accounts into a course with the course role; enrolled accounts are skipped.
     *
     * @param int $courseid Target course ID.
     * @return int Number of new enrolments.
     */
    public static function enrol_pool_into_course(int $courseid): int {
        global $DB;

        $roleid = self::ensure_role();

        $enrol = enrol_get_plugin('manual');
        $instance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'manual'], '*', IGNORE_MULTIPLE);
        if (!$instance) {
            $instanceid = $enrol->add_default_instance(get_course($courseid));
            $instance = $DB->get_record('enrol', ['id' => $instanceid]);
        }

        $context = \context_course::instance($courseid);
        $enrolled = 0;
        foreach (self::get_pool_usernames() as $username) {
            $user = $DB->get_record('user', ['username' => $username, 'deleted' => 0], 'id', IGNORE_MISSING);
            if (!$user || is_enrolled($context, $user->id, '', true)) {
                continue;
            }
            $enrol->enrol_user($instance, $user->id, $roleid);
            $enrolled++;
        }
        return $enrolled;
    }

    /**
     * Enrol the pool accounts into all courses that hold public learning objects.
     *
     * @return int Total number of new enrolments.
     */
    public static function enrol_pool_into_scope_courses(): int {
        $total = 0;
        foreach (object_scope::course_ids() as $courseid) {
            $total += self::enrol_pool_into_course($courseid);
        }
        return $total;
    }

    /**
     * Find the first free pool account.
     *
     * @return \stdClass|null User record, or null if all pool accounts are occupied.
     */
    public static function get_free_pool_account(): ?\stdClass {
        global $DB;

        $cache = \cache::make('block_lernobjektefilter', 'poolsessions');
        foreach (self::get_pool_usernames() as $username) {
            if ($cache->get(self::CACHE_PREFIX . $username) !== false) {
                continue;
            }
            $user = $DB->get_record('user', ['username' => $username, 'deleted' => 0], '*', IGNORE_MISSING);
            if ($user) {
                return $user;
            }
        }
        return null;
    }

    /**
     * Hand a free pool account to a visitor for a preview: enrol it into the course and occupy it.
     *
     * @param int $courseid Course of the learning object.
     * @return \stdClass|null The occupied pool account, or null if all are occupied.
     */
    public static function occupy_free_account(int $courseid): ?\stdClass {
        $account = self::get_free_pool_account();
        if (!$account) {
            return null;
        }
        self::enrol_pool_into_course($courseid);
        self::assign_system_role();
        self::mark_occupied($account->username);
        return $account;
    }

    /**
     * Mark a pool account as occupied and end its earlier sessions, so that two visitors never share it.
     *
     * @param string $username Pool account username.
     */
    public static function mark_occupied(string $username): void {
        global $DB;

        $userid = $DB->get_field('user', 'id', ['username' => $username, 'deleted' => 0]);
        if ($userid) {
            \core\session\manager::destroy_user_sessions($userid);
        }
        \cache::make('block_lernobjektefilter', 'poolsessions')->set(self::CACHE_PREFIX . $username, time());
    }

    /**
     * Release a pool account (logout).
     *
     * @param string $username Pool account username.
     */
    public static function mark_free(string $username): void {
        \cache::make('block_lernobjektefilter', 'poolsessions')->delete(self::CACHE_PREFIX . $username);
    }

    /**
     * Generate a random password nobody knows; pool accounts log in only through the preview.
     *
     * @return string
     */
    private static function generate_password(): string {
        return random_string(24) . 'Aa1!';
    }
}
