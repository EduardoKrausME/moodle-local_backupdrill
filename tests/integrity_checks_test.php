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
 * Component availability and native course-module evidence.
 *
 * @package    local_backupdrill
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_backupdrill;

defined('MOODLE_INTERNAL') || die();

/** @covers \local_backupdrill\check\structure_check */
final class integrity_checks_test extends \advanced_testcase {
    /** @return void */
    public function test_missing_modules_fail_and_references_are_not_claimed_verified(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $inventory = ['sections' => 0, 'modules' => ['assign' => 1],
            'files' => null, 'questioncategories' => null];
        $checks = \local_backupdrill\local\validation_engine::inspect($inventory, $course->id);
        $results = array_column($checks, 'status', 'id');
        $this->assertSame('failed', $results['mod_assign']);
        $this->assertSame('not_verified', $results['files_contenthash']);
        $this->assertSame('not_verified', $results['crossmodule_references']);
    }
}
