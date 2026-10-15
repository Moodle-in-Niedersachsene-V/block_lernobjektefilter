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

namespace block_lernobjektefilter\output;

use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for the block renderer (configured and unconfigured block, help icons, keyword chips).
 *
 * @package    block_lernobjektefilter
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(renderer::class)]
final class renderer_test extends \advanced_testcase {
    /**
     * The renderer of the plugin on a fresh page.
     *
     * @return renderer
     */
    private function renderer(): renderer {
        global $PAGE;
        $PAGE->set_url(new \moodle_url('/'));
        $PAGE->set_context(\context_system::instance());
        return $PAGE->get_renderer('block_lernobjektefilter');
    }

    public function test_unconfigured_block_shows_only_the_hint(): void {
        $this->resetAfterTest();
        set_config('categories', '', 'block_lernobjektefilter');

        $html = $this->renderer()->render_block(7);

        $this->assertStringContainsString(get_string('hint_notconfigured', 'block_lernobjektefilter'), $html);
        $this->assertStringNotContainsString('data-filter="searchterm"', $html);
    }

    public function test_configured_block_shows_filters_help_icons_and_chips(): void {
        // No user is logged in: released categories must be listed even without moodle/category:viewcourselist.
        $this->resetAfterTest();
        $category = $this->getDataGenerator()->create_category(['name' => 'Lernobjekte']);
        set_config('categories', (string)$category->id, 'block_lernobjektefilter');
        set_config('modtypes', 'page,scorm', 'block_lernobjektefilter');
        set_config('subjects', "Mathematik\nDeutsch", 'block_lernobjektefilter');
        set_config('customlabel', 'Thema', 'block_lernobjektefilter');
        set_config('customvalues', 'Bruchrechnung', 'block_lernobjektefilter');

        $html = $this->renderer()->render_block(7);

        $this->assertStringContainsString('id="lof-block-7"', $html);
        $this->assertStringContainsString('<option value="' . $category->id . '">Lernobjekte</option>', $html);
        $this->assertStringContainsString('data-value="scorm"', $html);
        $this->assertStringContainsString('data-group="subject" data-value="Mathematik"', $html);
        $this->assertStringContainsString('data-group="custom" data-value="Bruchrechnung"', $html);
        $this->assertStringContainsString('Thema', $html);
        // One help icon each for category, format, search term and the keyword lists.
        $this->assertGreaterThanOrEqual(6, substr_count($html, 'help-icon'));
    }
}
