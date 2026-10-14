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

defined('MOODLE_INTERNAL') || die();

/** Moodle question bank contexts changed across versions; give honest partial evidence. */
final class questions_check implements check {
    /** @inheritdoc */
    public function run(array $inventory, int $courseid): array {
        global $DB;
        $sql = 'SELECT COUNT(1) FROM {question_categories} qc
                  JOIN {context} c ON c.id = qc.contextid
                 WHERE (c.contextlevel = :courselevel AND c.instanceid = :courseid)
                    OR (c.contextlevel = :modlevel AND c.instanceid IN
                        (SELECT id FROM {course_modules} WHERE course = :courseid2))';
        $actual = (int) $DB->count_records_sql($sql, [
            'courselevel' => CONTEXT_COURSE, 'courseid' => $courseid,
            'modlevel' => CONTEXT_MODULE, 'courseid2' => $courseid,
        ]);
        $presence = ($inventory['questioncategories'] ?? 0) > 0 && $actual === 0 ? 'failed' : 'not_verified';
        return [[ 'id' => 'questioncategories_presence', 'status' => $presence,
            'expected' => $inventory['questioncategories'] ?? null, 'actual' => $actual],
            [ 'id' => 'questioncategories', 'status' => 'not_verified',
            'expected' => $inventory['questioncategories'] ?? null, 'actual' => $actual]];
    }
}
