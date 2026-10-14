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

/** @covers \local_backupdrill\local\validation_engine */
final class validation_engine_test extends \advanced_testcase {
    /** @return void */
    public function test_missing_components_cause_partial_status(): void {
        $check = \local_backupdrill\local\validation_engine::compare('assign', 2, 1);
        $this->assertSame('failed', $check['status']);
        $this->assertSame('partial', \local_backupdrill\local\validation_engine::classify([$check]));
    }

    /** @return void */
    public function test_unverifiable_is_not_success(): void {
        $check = \local_backupdrill\local\validation_engine::compare('references', null, null);
        $this->assertSame('not_verified', $check['status']);
        $this->assertSame('warnings', \local_backupdrill\local\validation_engine::classify([$check]));
    }

    /** @return void */
    public function test_exact_match_passes(): void {
        $this->assertSame('passed', \local_backupdrill\local\validation_engine::classify([
            \local_backupdrill\local\validation_engine::compare('sections', 3, 3),
        ]));
    }
}
