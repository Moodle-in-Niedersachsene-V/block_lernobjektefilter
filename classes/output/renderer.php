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

use block_lernobjektefilter\filter_groups;
use block_lernobjektefilter\object_scope;

/**
 * Renderer of the learning object filter block.
 *
 * @package   block_lernobjektefilter
 * @copyright 2026 Moodle in Niedersachsen e. V.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends \plugin_renderer_base {
    /**
     * Render the filter block.
     *
     * @param int $blockid Block instance ID.
     * @return string HTML output.
     */
    public function render_block(int $blockid): string {
        $categoryids = object_scope::category_ids();
        $modtypes = object_scope::modtypes();

        $categories = [['value' => '', 'label' => get_string('filter_all', 'block_lernobjektefilter')]];
        foreach ($this->released_categories($categoryids) as $id => $name) {
            $categories[] = ['value' => (string)$id, 'label' => $name];
        }

        $types = [];
        foreach ($modtypes as $modname) {
            $types[] = [
                'value'   => $modname,
                'label'   => get_string('modulename', 'mod_' . $modname),
                'iconurl' => $this->image_url('monologo', 'mod_' . $modname)->out(false),
            ];
        }

        $groups = [];
        foreach (filter_groups::groups() as $group) {
            $groups[] = [
                'key'    => $group['key'],
                'label'  => $group['label'],
                'values' => $group['values'],
                'help'   => $this->help($group['key'] === 'custom' ? 'label_custom' : 'label_' . $group['key']),
            ];
        }

        return $this->render_from_template('block_lernobjektefilter/block', [
            'blockid'        => $blockid,
            'configured'     => $categoryids && $modtypes,
            'categories'     => $categories,
            'modtypes'       => $types,
            'groups'         => $groups,
            'helpcategory'   => $this->help('label_category'),
            'helpmodtype'    => $this->help('label_modtype'),
            'helpsearchterm' => $this->help('label_searchterm'),
        ]);
    }

    /**
     * Names of the released course categories in course category order.
     *
     * The list comes from the settings, not from make_categories_list(): that one shows only categories the
     * viewer may browse (moodle/category:viewcourselist), so visitors without login would see none.
     *
     * @param int[] $categoryids Released category IDs.
     * @return array Category ID => nested name.
     */
    private function released_categories(array $categoryids): array {
        $sorted = [];
        foreach ($categoryids as $id) {
            $category = \core_course_category::get($id, IGNORE_MISSING, true);
            if ($category) {
                $sorted[$id] = [(int)$category->sortorder, $category->get_nested_name(false)];
            }
        }
        uasort($sorted, fn($a, $b) => $a[0] <=> $b[0]);
        return array_map(fn($entry) => $entry[1], $sorted);
    }

    /**
     * Template context of a help icon (blue question mark) with an explanation from the language file.
     *
     * @param string $identifier String identifier; the explanation is the string with suffix _help.
     * @return \stdClass Context for the core/help_icon template.
     */
    private function help(string $identifier): \stdClass {
        return (new \core\output\help_icon($identifier, 'block_lernobjektefilter'))->export_for_template($this);
    }
}
