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

namespace local_backupdrill\check;

/**
 * Validates native course-section and course-module records.
 */
final class structure_check implements check {
    /**
     * Handle run.
     */
    public function run(array $inventory, int $courseid): array {
        global $DB;
        $sections = (int) $DB->count_records('course_sections', ['course' => $courseid]);
        // Section zero is Moodle's built-in general section, not always listed in the backup manifest.
        $sectionexpected = $inventory['sections'] ?? null;
        $results = [\local_backupdrill\local\validation_engine::compare('sections',
            $sectionexpected, $sections)];
        $sql = 'SELECT m.name AS name, COUNT(cm.id) AS total
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module
                 WHERE cm.course = :courseid GROUP BY m.name';
        $counts = $DB->get_records_sql($sql, ['courseid' => $courseid]);
        foreach ($inventory['modules'] ?? [] as $name => $count) {
            $results[] = \local_backupdrill\local\validation_engine::compare('mod_' . $name,
                $count, isset($counts[$name]) ? (int) $counts[$name]->total : 0);
        }
        return $results;
    }
}
