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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * /**.
 *
 * @package    local_backupdrill
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_externalpage('local_backupdrill',
        get_string('pluginname', 'local_backupdrill'),
        new moodle_url('/local/backupdrill/index.php'), 'local/backupdrill:viewreports'));

    $settings = new admin_settingpage('local_backupdrill_settings',
        get_string('settings', 'local_backupdrill'));
    $settings->add(new admin_setting_configtext('local_backupdrill/retentiondays',
        get_string('retentiondays', 'local_backupdrill'), '', 90, PARAM_INT));
    $settings->add(new admin_setting_configtext('local_backupdrill/labname',
        get_string('labname', 'local_backupdrill'), '', '', PARAM_TEXT));
    $settings->add(new admin_setting_configtext('local_backupdrill/fingerprint',
        get_string('fingerprint', 'local_backupdrill'), '', '', PARAM_ALPHANUM));
    $ADMIN->add('localplugins', $settings);
}
