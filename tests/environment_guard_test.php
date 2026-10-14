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

namespace local_backupdrill;

/**
 * Handle environment guard test.
 *
 * @covers \local_backupdrill\local\environment_guard
 */
final class environment_guard_test extends \advanced_testcase {
    /**
     * Verify refuses environments without explicit enrolment.
     *
     * @return void
     */
    public function test_refuses_environments_without_explicit_enrolment(): void {
        if (defined('LOCAL_BACKUPDRILL_LAB_ENABLED') && LOCAL_BACKUPDRILL_LAB_ENABLED === true) {
            $this->markTestSkipped('Dedicated lab is explicitly opted in; do not test default denial there.');
        }
        $this->expectException(\moodle_exception::class);
        \local_backupdrill\local\environment_guard::assert_safe();
    }
}
