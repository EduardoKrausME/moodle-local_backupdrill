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

namespace local_backupdrill\task;

/**
 * Deletes only history with no remaining owned course and no active task.
 */
final class prune_history extends \core\task\scheduled_task {
    /**
     * Handle get name.
     *
     * @return string Human task name.
     */
    public function get_name(): string {
        return get_string('taskprune', 'local_backupdrill');
    }

    /**
     * Handle execute.
     *
     * @return void
     */
    public function execute(): void {
        global $DB;
        \local_backupdrill\local\environment_guard::assert_safe();
        $days = (int) get_config('local_backupdrill', 'retentiondays');
        $days = \local_backupdrill\local\retention_policy::days($days);
        $cutoff = time() - ($days * DAYSECS);
        $sql = 'SELECT r.id FROM {local_backupdrill_run} r
                 WHERE r.timecreated < :cutoff
                   AND r.status NOT IN (:pending, :running)
                   AND NOT EXISTS (SELECT 1 FROM {local_backupdrill_art} a
                                    WHERE a.runid = r.id AND a.deletedat = 0)';
        $runs = $DB->get_records_sql($sql, ['cutoff' => $cutoff,
            'pending' => 'pending', 'running' => 'running'], 0, 100);
        foreach ($runs as $run) {
            $DB->delete_records('local_backupdrill_art', ['runid' => $run->id]);
            $DB->delete_records('local_backupdrill_run', ['id' => $run->id]);
        }
    }
}
