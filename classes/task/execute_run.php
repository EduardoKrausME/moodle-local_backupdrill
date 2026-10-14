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
 * Execute an individual real restore through Moodle's CLI cron.
 *
 * @package    local_backupdrill
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_backupdrill\task;

defined('MOODLE_INTERNAL') || die();

/** Never run on non-CLI PHP or on unregistered environments. */
final class execute_run extends \core\task\adhoc_task {
    /** @return string Task name. */
    public function get_name(): string {
        return get_string('taskexecute', 'local_backupdrill');
    }

    /** @return void */
    public function execute(): void {
        \local_backupdrill\local\environment_guard::assert_safe();
        $data = $this->get_custom_data();
        if (empty($data->runid)) {
            throw new \moodle_exception('invalidrun', 'local_backupdrill');
        }
        global $DB;
        $run = $DB->get_record('local_backupdrill_run', ['id' => (int) $data->runid]);
        if (!$run || $run->status !== 'pending') {
            return;
        }
        \local_backupdrill\local\restore_executor::execute((int) $data->runid);
    }
}
