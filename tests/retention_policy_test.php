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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * /**.
 *
 * @package    local_backupdrill
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_backupdrill;

/**
 * Handle retention policy test.
 *
 * @covers \local_backupdrill\local\retention_policy
 */
final class retention_policy_test extends \advanced_testcase {
    /**
     * Verify enforces minimum and maximum.
     *
     * @return void
     */
    public function test_enforces_minimum_and_maximum(): void {
        $this->assertSame(7, \local_backupdrill\local\retention_policy::days(1));
        $this->assertSame(90, \local_backupdrill\local\retention_policy::days(90));
        $this->assertSame(3650, \local_backupdrill\local\retention_policy::days(999999));
    }
}
