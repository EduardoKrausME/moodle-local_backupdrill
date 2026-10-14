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

defined('MOODLE_INTERNAL') || die();

/** @covers \local_backupdrill\local\drill_configuration */
final class drill_configuration_test extends \advanced_testcase {
    /** @return void */
    public function test_accepts_bounded_configuration(): void {
        $plan = (object) ['name' => 'Small fictional course', 'intervalhours' => 24,
            'maxseconds' => 600, 'maxbytes' => 52428800, 'maxattempts' => 2];
        \local_backupdrill\local\drill_configuration::validate($plan);
        $this->assertTrue(true);
    }

    /** @return void */
    public function test_rejects_unbounded_timeout(): void {
        $plan = (object) ['name' => 'Abusive plan', 'intervalhours' => 1,
            'maxseconds' => 9999999, 'maxbytes' => 52428800, 'maxattempts' => 2];
        $this->expectException(\moodle_exception::class);
        \local_backupdrill\local\drill_configuration::validate($plan);
    }
}
