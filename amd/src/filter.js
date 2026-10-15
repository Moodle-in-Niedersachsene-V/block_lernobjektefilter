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

/**
 * Learning object search and result cards of the learning object filter block.
 *
 * @module     block_lernobjektefilter/filter
 * @copyright  2026 Moodle in Niedersachsen e. V.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Notification from 'core/notification';
import Templates from 'core/templates';
import {getString} from 'core/str';
import Config from 'core/config';

/** Delay after the last input before the search runs, in milliseconds. */
const SEARCH_DELAY = 350;

/**
 * Initialise one block instance.
 *
 * @param {number} blockid Block instance ID.
 */
export const init = (blockid) => {
    const root = document.getElementById(`lof-block-${blockid}`);
    if (!root) {
        return;
    }
    const results = root.querySelector('[data-region="results"]');
    const category = root.querySelector('[data-filter="category"]');
    const searchterm = root.querySelector('[data-filter="searchterm"]');
    if (!results || !category || !searchterm) {
        // The block is not configured yet and shows only a hint.
        return;
    }
    const count = root.querySelector('[data-region="count"]');
    const spinner = root.querySelector('[data-region="spinner"]');
    const emptyResults = results.innerHTML;
    const emptyCount = count.textContent;
    const chosen = {modtype: '', tags: {}};
    let timer = null;
    let requestno = 0;

    const filters = () => ({
        category: parseInt(category.value, 10) || 0,
        modtype: chosen.modtype,
        tags: Object.values(chosen.tags).filter((value) => value !== ''),
        searchterm: searchterm.value.trim(),
    });

    const showEmpty = () => {
        results.innerHTML = emptyResults;
        count.textContent = emptyCount;
    };

    const renderResults = async(objects) => {
        const context = {objects: objects.map((object) => ({...object, hastags: object.tags.length > 0}))};
        const {html, js} = await Templates.renderForPromise('block_lernobjektefilter/results', context);
        Templates.replaceNodeContents(results, html, js);
        count.textContent = await getString('results_count', 'block_lernobjektefilter', objects.length);
        // The client-side template context has no sesskey, so the preview forms get it here.
        results.querySelectorAll('input[name="sesskey"]').forEach((input) => {
            input.value = Config.sesskey;
        });
    };

    const search = async() => {
        const args = filters();
        if (!args.category && !args.modtype && !args.tags.length && !args.searchterm) {
            showEmpty();
            return;
        }
        const thisrequest = ++requestno;
        spinner.classList.remove('d-none');
        try {
            const result = await Ajax.call([{methodname: 'block_lernobjektefilter_search_objects', args}])[0];
            // A slower earlier answer must not overwrite the result of a newer search.
            if (thisrequest === requestno) {
                await renderResults(result.objects);
            }
        } catch (error) {
            results.innerHTML = '';
            await Notification.exception(error);
        } finally {
            if (thisrequest === requestno) {
                spinner.classList.add('d-none');
            }
        }
    };

    const scheduleSearch = () => {
        clearTimeout(timer);
        timer = setTimeout(search, SEARCH_DELAY);
    };

    const toggleChip = (chip) => {
        const filter = chip.dataset.filter;
        const group = filter === 'tag' ? chip.dataset.group : 'modtype';
        const current = filter === 'tag' ? (chosen.tags[group] || '') : chosen.modtype;
        const isActive = current === chip.dataset.value;
        const selector = filter === 'tag'
            ? `.lof-chip[data-filter="tag"][data-group="${CSS.escape(group)}"]`
            : '.lof-chip[data-filter="modtype"]';
        root.querySelectorAll(selector).forEach((other) => setChip(other, false));
        setChip(chip, !isActive);
        const value = isActive ? '' : chip.dataset.value;
        if (filter === 'tag') {
            chosen.tags[group] = value;
        } else {
            chosen.modtype = value;
        }
    };

    root.addEventListener('click', (e) => {
        const chip = e.target.closest('.lof-chip');
        if (chip) {
            toggleChip(chip);
            scheduleSearch();
        } else if (e.target.closest('[data-action="reset"]')) {
            clearTimeout(timer);
            requestno++;
            category.value = '';
            searchterm.value = '';
            chosen.modtype = '';
            chosen.tags = {};
            root.querySelectorAll('.lof-chip').forEach((other) => setChip(other, false));
            spinner.classList.add('d-none');
            showEmpty();
        }
    });
    category.addEventListener('change', scheduleSearch);
    searchterm.addEventListener('input', scheduleSearch);
};

/**
 * Show a chip as chosen or not.
 *
 * @param {HTMLElement} chip Chip button.
 * @param {boolean} isActive Whether the chip is chosen.
 */
const setChip = (chip, isActive) => {
    chip.classList.toggle('lof-chip-active', isActive);
    chip.setAttribute('aria-pressed', isActive ? 'true' : 'false');
};
