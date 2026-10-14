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
 * Handle report builder test.
 *
 * @covers \local_backupdrill\local\report_builder
 */
final class report_builder_test extends \advanced_testcase {
    /**
     * Verify csv exports comparable evidence.
     *
     * @return void
     */
    public function test_csv_exports_comparable_evidence(): void {
        $report = \local_backupdrill\local\report_builder::start(13);
        $report['status'] = 'partial';
        $report['checks'][] = ['id' => 'sections', 'status' => 'failed', 'expected' => 3, 'actual' => 2];
        $csv = \local_backupdrill\local\report_builder::csv($report);
        $this->assertStringContainsString('sections', $csv);
        $this->assertStringContainsString('failed', $csv);
        $this->assertStringNotContainsString('firstname', $csv);
    }
}
