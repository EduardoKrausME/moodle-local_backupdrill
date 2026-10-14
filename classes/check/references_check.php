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

namespace local_backupdrill\check;

/**
 * Checks basic availability JSON but does not claim full reference resolution.
 */
final class references_check implements check {
    /**
     * Handle run.
     */
    public function run(array $inventory, int $courseid): array {
        global $DB;
        $modules = $DB->get_records('course_modules', ['course' => $courseid], '',
            'id,availability,completion');
        $invalid = 0;
        $missingreferences = 0;
        foreach ($modules as $module) {
            if ($module->availability === null || $module->availability === '') {
                continue;
            }
            $availability = json_decode($module->availability, true);
            if (!is_array($availability)) {
                $invalid++;
                continue;
            }
            $missingreferences += self::missing_completion_references($availability, $modules);
        }
        return [
            ['id' => 'availability_json', 'status' => $invalid ? 'failed' : 'passed',
                'expected' => 0, 'actual' => $invalid],
            ['id' => 'availability_completion_references',
                'status' => $missingreferences ? 'failed' : 'passed',
                'expected' => 0, 'actual' => $missingreferences],
            ['id' => 'completion_semantics', 'status' => 'not_verified',
                'expected' => null, 'actual' => null],
            ['id' => 'crossmodule_references', 'status' => 'not_verified',
                'expected' => null, 'actual' => null],
            ['id' => 'course_settings', 'status' => 'not_verified',
                'expected' => null, 'actual' => null],
        ];
    }

    /**
     * Handle missing completion references.
     *
     * @param array $node Decoded availability tree.
     * @param array $modules Course module records.
     * @return int Number of dangling completion references.
     */
    private static function missing_completion_references(array $node, array $modules): int {
        $count = 0;
        if (($node['type'] ?? '') === 'completion' && !empty($node['cm']) &&
                !array_key_exists((int) $node['cm'], $modules)) {
            $count++;
        }
        foreach ($node['c'] ?? [] as $child) {
            if (is_array($child)) {
                $count += self::missing_completion_references($child, $modules);
            }
        }
        return $count;
    }
}
