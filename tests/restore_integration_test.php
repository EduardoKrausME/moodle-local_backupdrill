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
 * Handle restore integration test.
 *
 * @coversNothing
 */
final class restore_integration_test extends \advanced_testcase {
    /**
     * Verify real backup and restore of fictional course.
     *
     * @return void
     */
    public function test_real_backup_and_restore_of_fictional_course(): void {
        global $CFG, $USER;
        if (getenv('BACKUPDRILL_RUN_INTEGRATION') !== '1') {
            $this->markTestSkipped('Set BACKUPDRILL_RUN_INTEGRATION=1 on a disposable Moodle test DB.');
        }
        $this->resetAfterTest(true);
        $this->setAdminUser();
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
        $generator = $this->getDataGenerator();
        $category = $generator->create_category();
        $original = $generator->create_course(['category' => $category->id]);
        $generator->create_module('assign', ['course' => $original->id]);
        $generator->create_module('quiz', ['course' => $original->id]);
        $sourcecontext = \context_course::instance($original->id);
        get_file_storage()->create_file_from_string([
            'contextid' => $sourcecontext->id, 'component' => 'course',
            'filearea' => 'overviewfiles', 'itemid' => 0, 'filepath' => '/',
            'filename' => 'fixture.txt', 'userid' => $USER->id,
        ], 'Fictional course content');
        $bc = new \backup_controller(\backup::TYPE_1COURSE, $original->id, \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO, \backup::MODE_GENERAL, $USER->id);
        $bc->execute_plan();
        $backupid = $bc->get_backupid();
        $bc->destroy();
        $archives = get_file_storage()->get_area_files($sourcecontext->id,
            'backup', 'course', 0, 'id DESC', false);
        $archive = reset($archives);
        $this->assertNotFalse($archive);
        $unpackdir = make_backup_temp_directory($backupid);
        $this->assertTrue(get_file_packer('application/vnd.moodle.backup')->extract_to_pathname(
            $archive, $unpackdir));
        $restoredid = \restore_dbops::create_new_course('Drill fixture', 'BDRL-FIXTURE', $category->id);
        $rc = new \restore_controller($backupid, $restoredid, \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL, $USER->id, \backup::TARGET_NEW_COURSE);
        $this->assertTrue($rc->execute_precheck());
        $rc->execute_plan();
        $rc->destroy();
        $this->assertGreaterThanOrEqual(2,
            $GLOBALS['DB']->count_records('course_modules', ['course' => $restoredid]));
    }
}
