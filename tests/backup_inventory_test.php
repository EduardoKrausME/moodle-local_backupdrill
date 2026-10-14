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
 * Handle backup inventory test.
 *
 * @covers \local_backupdrill\local\backup_inventory
 */
final class backup_inventory_test extends \advanced_testcase {
    /**
     * Verify reads minimal manifest and marks missing as unknown.
     *
     * @return void
     */
    public function test_reads_minimal_manifest_and_marks_missing_as_unknown(): void {
        $dir = make_temp_directory('backupdrill_test_' . random_string(8));
        file_put_contents($dir . '/moodle_backup.xml', '<moodle_backup><information><format>moodle2</format>' .
            '<contents><sections><section id="1"/></sections><activities>' .
            '<activity><modulename>assign</modulename></activity>' .
            '</activities></contents></information></moodle_backup>');
        $inventory = \local_backupdrill\local\backup_inventory::inspect($dir);
        $this->assertSame(1, $inventory['sections']);
        $this->assertSame(1, $inventory['modules']['assign']);
        $this->assertNull($inventory['files']);
        fulldelete($dir);
    }

    /**
     * Verify rejects missing metadata.
     *
     * @return void
     */
    public function test_rejects_missing_metadata(): void {
        $this->expectException(\moodle_exception::class);
        \local_backupdrill\local\backup_inventory::inspect(sys_get_temp_dir());
    }
}
