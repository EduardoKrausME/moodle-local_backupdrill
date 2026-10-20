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

/**
 * /**.
 *
 * @package    local_backupdrill
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_backupdrill\check;

/**
 * Detect declared activity modules that are not usable in the laboratory.
 */
final class plugins_check implements check {
    /**
     * Handle run.
     */
    public function run(array $inventory, int $courseid): array {
        $results = [];
        $manager = \core_plugin_manager::instance();
        foreach (array_keys($inventory['modules'] ?? []) as $name) {
            $plugin = $manager->get_plugin_info('mod_' . $name);
            $results[] = ['id' => 'plugin_' . $name,
                'status' => $plugin !== null && !empty($plugin->versiondb) ? 'passed' : 'failed',
                'expected' => 'installed', 'actual' => $plugin === null || empty($plugin->versiondb) ? 'missing' : 'installed'];
        }
        foreach (($inventory['questiontypes'] ?? []) as $name) {
            $plugin = $manager->get_plugin_info('qtype_' . $name);
            $present = $plugin !== null && !empty($plugin->versiondb);
            $results[] = ['id' => 'qtype_' . $name,
                'status' => $present ? 'passed' : 'failed',
                'expected' => 'installed', 'actual' => $present ? 'installed' : 'missing'];
        }
        return $results;
    }
}
