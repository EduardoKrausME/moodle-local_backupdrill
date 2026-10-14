<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * /**.
 *
 * @package    local_backupdrill
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Register an administrator-only navigation entry.
 *
 * @param settings_navigation $settingsnav Navigation tree.
 * @param context $context Current context.
 * @return void
 */
function local_backupdrill_extend_settings_navigation(settings_navigation $settingsnav, context $context): void {
    if (has_capability('local/backupdrill:viewreports', context_system::instance())) {
        $settingsnav->add(get_string('pluginname', 'local_backupdrill'),
            new moodle_url('/local/backupdrill/index.php'));
    }
}
