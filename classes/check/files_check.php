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
 * Counts restored file records, excluding directories.
 */
final class files_check implements check {
    /**
     * Handle run.
     */
    public function run(array $inventory, int $courseid): array {
        global $DB;
        $sql = 'SELECT COUNT(1)
                  FROM {files} f JOIN {context} c ON c.id = f.contextid
                 WHERE f.filename <> :directory AND (
                       (c.contextlevel = :courselevel AND c.instanceid = :courseid)
                       OR (c.contextlevel = :modlevel AND c.instanceid IN
                           (SELECT id FROM {course_modules} WHERE course = :courseid2)))';
        $actual = (int) $DB->count_records_sql($sql, [
            'directory' => '.', 'courselevel' => CONTEXT_COURSE, 'courseid' => $courseid,
            'modlevel' => CONTEXT_MODULE, 'courseid2' => $courseid,
        ]);
        // Backup file records are not a 1:1 correspondence with filtered restored files.
        // Preserve evidence, but do not claim byte-perfect verification.
        $payloadstatus = !isset($inventory['files']) || !($inventory['filepayloadchecked'] ?? 0)
            ? 'not_verified' : (($inventory['filepayloadcorrupt'] ?? 0) > 0 ? 'failed' : 'passed');
        return [[ 'id' => 'backup_file_payload_sha1', 'status' => $payloadstatus,
            'expected' => 0, 'actual' => $inventory['filepayloadcorrupt'] ?? null],
            [ 'id' => 'files_count', 'status' => 'not_verified',
            'expected' => $inventory['files'] ?? null, 'actual' => $actual],
            [ 'id' => 'files_contenthash', 'status' => 'not_verified',
                'expected' => null, 'actual' => null]];
    }
}
