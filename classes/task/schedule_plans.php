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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * Recurrence management in a restricted laboratory.
 *
 * @package    local_backupdrill
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_backupdrill\task;

defined('MOODLE_INTERNAL') || die();

/** Avoids issuing any new work until the lab guard has passed. */
final class schedule_plans extends \core\task\scheduled_task {
    /** @return string Localized task name. */
    public function get_name(): string {
        return get_string('taskschedule', 'local_backupdrill');
    }

    /** @return void */
    public function execute(): void {
        global $DB;
        \local_backupdrill\local\environment_guard::assert_safe();
        $now = time();
        $plans = $DB->get_records_select('local_backupdrill_plan',
            'enabled = :enabled AND nextrun <= :now', ['enabled' => 1, 'now' => $now], 'id ASC', '*', 0, 20);
        foreach ($plans as $plan) {
            \local_backupdrill\local\drill_configuration::validate($plan);
            $lock = \core\lock\lock_config::get_lock_factory('local_backupdrill')->get_lock('plan-' . $plan->id, 0, 5);
            if (!$lock) {
                continue;
            }
            try {
                $fresh = $DB->get_record('local_backupdrill_plan', ['id' => $plan->id]);
                if ($fresh && $fresh->enabled && $fresh->nextrun <= time()) {
                    $fresh->nextrun = time() + ($fresh->intervalhours * HOURSECS);
                    $fresh->timemodified = time();
                    $DB->update_record('local_backupdrill_plan', $fresh);
                    \local_backupdrill\local\execution_history::queue((int) $fresh->id);
                }
            } finally {
                $lock->release();
            }
        }
    }
}
