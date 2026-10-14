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

namespace local_backupdrill;

/**
 * Handle lock and history test.
 *
 * @covers \local_backupdrill\local\execution_history
 */
final class lock_and_history_test extends \advanced_testcase {
    /**
     * Verify queue creates unique pending records without dispatch.
     *
     * @return void
     */
    public function test_queue_creates_unique_pending_records_without_dispatch(): void {
        global $DB;
        $this->resetAfterTest();
        $now = time();
        $plan = (object) [
            'name' => 'Safe fictional test', 'fileid' => 0,
            'enabled' => 0, 'intervalhours' => 24, 'nextrun' => 0,
            'maxseconds' => 1800, 'maxbytes' => 536870912,
            'maxattempts' => 2, 'autocleanup' => 0,
            'timecreated' => $now, 'timemodified' => $now,
        ];
        $id = $DB->insert_record('local_backupdrill_plan', $plan);
        $first = \local_backupdrill\local\execution_history::queue($id, false);
        $second = \local_backupdrill\local\execution_history::queue($id, false);
        $this->assertNotEquals($first, $second);
        $this->assertEquals('pending', $DB->get_field('local_backupdrill_run', 'status', ['id' => $first]));
        $this->assertEquals('pending', $DB->get_field('local_backupdrill_run', 'status', ['id' => $second]));
    }

    /**
     * Verify lock api returns a scoped lock.
     *
     * @return void
     */
    public function test_lock_api_returns_a_scoped_lock(): void {
        $lock = \core\lock\lock_config::get_lock_factory('local_backupdrill')->get_lock('unit-check', 0, 5);
        $this->assertNotFalse($lock);
        $lock->release();
    }
}
