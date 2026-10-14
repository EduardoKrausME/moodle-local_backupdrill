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
 * Persist queued work before dispatching it to Moodle's Task API.
 */
final class execution_history {
    /**
     * Handle queue.
     *
     * @param int $planid Plan to queue.
     * @return int Run identifier.
     */
    public static function queue(int $planid, bool $dispatch = true): int {
        global $DB;
        $plan = $DB->get_record('local_backupdrill_plan', ['id' => $planid], '*', MUST_EXIST);
        drill_configuration::validate($plan);
        $run = (object) [
            'planid' => $planid, 'status' => 'pending', 'attempt' => 0,
            'courseid' => 0, 'started' => 0, 'finished' => 0,
            'errorcode' => '', 'reportjson' => null, 'timecreated' => time(),
        ];
        $runid = $DB->insert_record('local_backupdrill_run', $run);
        if ($dispatch) {
            self::dispatch($runid);
        }
        return $runid;
    }

    /**
     * Handle dispatch.
     *
     * @param int $runid Queued record.
     */
    public static function dispatch(int $runid): void {
        $task = new \local_backupdrill\task\execute_run();
        $task->set_custom_data(['runid' => $runid]);
        \core\task\manager::queue_adhoc_task($task, true);
    }

    /**
     * Handle finish.
     *
     * @param int $runid Record identifier.
     * @param array $report Sanitized report.
     */
    public static function finish(int $runid, array $report): void {
        global $DB;
        $run = $DB->get_record('local_backupdrill_run', ['id' => $runid], '*', MUST_EXIST);
        $run->status = $report['status'];
        $run->errorcode = $report['errorcode'] ?? '';
        $run->reportjson = json_encode($report, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        $run->finished = time();
        $DB->update_record('local_backupdrill_run', $run);
        \local_backupdrill\event\drill_completed::create([
            'context' => \context_system::instance(), 'objectid' => $runid,
            'other' => ['result' => $run->status],
        ])->trigger();
    }
}
