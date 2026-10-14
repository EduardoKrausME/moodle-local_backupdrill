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

namespace local_backupdrill\local;

/**
 * Mandatory checks may never silently convert missing evidence into success.
 */
final class validation_engine {
    /**
     * Handle inspect.
     *
     * @param array $inventory Manifest.
     * @param int $courseid Temporary course.
     * @return array Checks.
     */
    public static function inspect(array $inventory, int $courseid): array {
        $checks = [new \local_backupdrill\check\structure_check(),
            new \local_backupdrill\check\plugins_check(),
            new \local_backupdrill\check\files_check(),
            new \local_backupdrill\check\questions_check(),
            new \local_backupdrill\check\references_check()];
        $results = [];
        foreach ($checks as $check) {
            foreach ($check->run($inventory, $courseid) as $result) {
                $results[] = $result;
            }
        }
        return $results;
    }

    /**
     * Handle classify.
     *
     * @param array $results Check results.
     * @return string Honest overall status.
     */
    public static function classify(array $results): string {
        $statuses = array_column($results, 'status');
        if (in_array('failed', $statuses, true)) {
            return 'partial';
        }
        if (in_array('warning', $statuses, true) || in_array('not_verified', $statuses, true)) {
            return 'warnings';
        }
        return $results === [] ? 'partial' : 'passed';
    }

    /**
     * Handle compare.
     *
     * @param string $id Check ID.
     * @param mixed $expected Expected quantity.
     * @param mixed $actual Actual quantity.
     * @return array Comparable result.
     */
    public static function compare(string $id, $expected, $actual): array {
        if ($expected === null || $actual === null) {
            $status = 'not_verified';
        } else if ($actual < $expected) {
            $status = 'failed';
        } else if ($actual > $expected) {
            $status = 'warning';
        } else {
            $status = 'passed';
        }
        return compact('id', 'status', 'expected', 'actual');
    }
}
