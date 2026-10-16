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
 * Text excerpt of the content of a learning object for the AI tagging.
 *
 * Only teaching content is read: titles, texts and file names. Author IDs, user data and
 * Moodle-internal identifiers are never part of the excerpt.
 *
 * @package   block_lernobjektefilter
 * @copyright 2026 Moodle in Niedersachsen e. V.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content_extractor {
    /** Maximum length of the whole excerpt. */
    public const MAX_LENGTH = 3000;

    /** Maximum length of a single text part (e.g. one glossary definition). */
    public const PART_LENGTH = 200;

    /** Maximum number of parts (entries, chapters, pages, files) per learning object. */
    public const MAX_PARTS = 40;

    /** File areas whose file names describe the content, per activity type. */
    public const FILE_AREAS = [
        'folder'      => 'content',
        'h5pactivity' => 'package',
        'imscp'       => 'content',
        'resource'    => 'content',
        'scorm'       => 'package',
    ];

    /**
     * Excerpt of the content of a learning object.
     *
     * @param \stdClass $object Learning object with cmid, modname and instance.
     * @return string Plain text excerpt, at most MAX_LENGTH characters; empty if the type has no readable content.
     */
    public static function extract(\stdClass $object): string {
        $parts = array_merge(self::content_parts($object), self::file_names($object));
        $text = '';
        foreach ($parts as $part) {
            $part = self::clean($part);
            if ($part === '') {
                continue;
            }
            $text .= ($text === '' ? '' : "\n") . $part;
            if (\core_text::strlen($text) >= self::MAX_LENGTH) {
                return \core_text::substr($text, 0, self::MAX_LENGTH);
            }
        }
        return $text;
    }

    /**
     * Content parts of the activity types that keep text in own tables.
     *
     * @param \stdClass $object Learning object.
     * @return string[] Text parts.
     */
    private static function content_parts(\stdClass $object): array {
        global $DB;

        $instance = (int)$object->instance;
        switch ($object->modname) {
            case 'glossary':
                $entries = $DB->get_records(
                    'glossary_entries',
                    ['glossaryid' => $instance, 'approved' => 1],
                    'concept ASC',
                    'id, concept, definition',
                    0,
                    self::MAX_PARTS
                );
                return array_map(fn($e) => $e->concept . ': ' . self::short($e->definition), array_values($entries));

            case 'book':
                $chapters = $DB->get_records(
                    'book_chapters',
                    ['bookid' => $instance, 'hidden' => 0],
                    'pagenum ASC',
                    'id, title, content',
                    0,
                    self::MAX_PARTS
                );
                return array_map(fn($c) => $c->title . ': ' . self::short($c->content), array_values($chapters));

            case 'lesson':
                $pages = $DB->get_records(
                    'lesson_pages',
                    ['lessonid' => $instance],
                    'id ASC',
                    'id, title, contents',
                    0,
                    self::MAX_PARTS
                );
                return array_map(fn($p) => $p->title . ': ' . self::short($p->contents), array_values($pages));

            case 'page':
                return [self::short((string)$DB->get_field('page', 'content', ['id' => $instance]), self::MAX_LENGTH)];

            case 'scorm':
                $scoes = $DB->get_records(
                    'scorm_scoes',
                    ['scorm' => $instance],
                    'sortorder ASC, id ASC',
                    'id, title',
                    0,
                    self::MAX_PARTS
                );
                return array_values(array_map(fn($s) => (string)$s->title, $scoes));

            case 'url':
                return [(string)$DB->get_field('url', 'externalurl', ['id' => $instance])];

            default:
                return [];
        }
    }

    /**
     * Names of the files of the learning object, for types whose files describe the content.
     *
     * @param \stdClass $object Learning object.
     * @return string[] File names.
     */
    private static function file_names(\stdClass $object): array {
        if (!isset(self::FILE_AREAS[$object->modname])) {
            return [];
        }
        $context = \context_module::instance($object->cmid, IGNORE_MISSING);
        if (!$context) {
            return [];
        }
        $files = get_file_storage()->get_area_files(
            $context->id,
            'mod_' . $object->modname,
            self::FILE_AREAS[$object->modname],
            false,
            'filepath, filename',
            false
        );
        $names = [];
        foreach ($files as $file) {
            $names[] = get_string('file') . ': ' . $file->get_filename();
            if (count($names) >= self::MAX_PARTS) {
                break;
            }
        }
        return $names;
    }

    /**
     * Plain text of an HTML part, shortened.
     *
     * @param string|null $html HTML or plain text.
     * @param int $length Maximum length.
     * @return string Plain text.
     */
    private static function short(?string $html, int $length = self::PART_LENGTH): string {
        $text = self::clean(html_to_text((string)$html, 0, false));
        return \core_text::strlen($text) > $length ? \core_text::substr($text, 0, $length) . '…' : $text;
    }

    /**
     * Collapse white space.
     *
     * @param string $text Text.
     * @return string Text on one line, trimmed.
     */
    private static function clean(string $text): string {
        return trim(preg_replace('/\s+/u', ' ', $text));
    }
}
