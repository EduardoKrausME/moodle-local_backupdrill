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

namespace local_backupdrill\local;

/**
 * Restore is intentionally never exposed by a web request or remote production API.
 */
final class restore_executor {
    /**
     * Handle execute.
     *
     * @param int $runid Previously queued run.
     * @return array Final technical report.
     */
    public static function execute(int $runid): array {
        global $CFG, $DB;
        environment_guard::assert_safe();
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
        $run = $DB->get_record('local_backupdrill_run', ['id' => $runid], '*', MUST_EXIST);
        if ($run->status !== 'pending') {
            throw new \moodle_exception('alreadyrunning', 'local_backupdrill');
        }
        $plan = $DB->get_record('local_backupdrill_plan', ['id' => $run->planid], '*', MUST_EXIST);
        drill_configuration::validate($plan);
        $file = get_file_storage()->get_file_by_id($plan->fileid);
        if (!$file || $file->is_directory() || $file->get_component() !== 'local_backupdrill' ||
                $file->get_filearea() !== 'source' || (int) $file->get_itemid() !== (int) $plan->id ||
                !preg_match('/\.mbz$/i', $file->get_filename()) || $file->get_filesize() > $plan->maxbytes) {
            $report = report_builder::start($runid);
            $report['status'] = 'read_failed';
            $report['errorcode'] = 'invalid_source';
            execution_history::finish($runid, $report);
            return $report;
        }
        $lockfactory = \core\lock\lock_config::get_lock_factory('local_backupdrill');
        $lock = $lockfactory->get_lock('mbz-' . $file->get_contenthash(), 0, $plan->maxseconds);
        if (!$lock) {
            // Moodle will retry the adhoc task, while the record remains pending.
            throw new \moodle_exception('lockunavailable', 'local_backupdrill');
        }
        $started = microtime(true);
        $report = report_builder::start($runid);
        $backupid = 'bd' . bin2hex(random_bytes(12));
        $courseid = 0;
        $phase = 'read_failed';
        $rc = null;
        try {
            $run->attempt++;
            $run->started = time();
            $run->status = 'running';
            $DB->update_record('local_backupdrill_run', $run);
            if ($run->attempt > $plan->maxattempts) {
                throw new \moodle_exception('attemptsexceeded', 'local_backupdrill');
            }
            self::deadline($started, $plan->maxseconds);
            $archivepath = make_temp_directory('local_backupdrill') . '/' . $backupid . '.mbz';
            if (!$file->copy_content_to($archivepath)) {
                throw new \moodle_exception('invalidarchive', 'local_backupdrill');
            }
            $unpackdir = make_backup_temp_directory($backupid);
            $packer = get_file_packer('application/vnd.moodle.backup');
            if (!$packer->extract_to_pathname($archivepath, $unpackdir)) {
                throw new \moodle_exception('invalidarchive', 'local_backupdrill');
            }
            self::check_extracted_tree($unpackdir, min($plan->maxbytes * 4, 8589934592));
            $report['inventory'] = backup_inventory::inspect($unpackdir);
            $report['checks'] = (new \local_backupdrill\check\plugins_check())->run(
                $report['inventory'], 0);
            foreach ($report['checks'] as $check) {
                if ($check['status'] === 'failed') {
                    $phase = 'restore_failed';
                    throw new \moodle_exception('missingplugin', 'local_backupdrill');
                }
            }
            self::deadline($started, $plan->maxseconds);
            $phase = 'restore_failed';
            $category = (int) $CFG->local_backupdrill_categoryid;
            if (!$category) {
                throw new \moodle_exception('nocategory', 'local_backupdrill');
            }
            $shortname = 'BDRL-' . $runid . '-' . bin2hex(random_bytes(6));
            $courseid = \restore_dbops::create_new_course('Backup drill ' . $runid, $shortname, (int) $category);
            cleanup_manager::register($runid, $courseid, $shortname);
            $DB->set_field('course', 'visible', 0, ['id' => $courseid]);
            $run->courseid = $courseid;
            $DB->update_record('local_backupdrill_run', $run);
            $rc = new \restore_controller($backupid, $courseid, \backup::INTERACTIVE_NO,
                \backup::MODE_GENERAL, (int) get_admin()->id, \backup::TARGET_NEW_COURSE);
            if (!$rc->execute_precheck()) {
                throw new \moodle_exception('restoreprecheckfailed', 'local_backupdrill');
            }
            $prechecks = $rc->get_precheck_results();
            if (!empty($prechecks['warnings'])) {
                $report['warnings'][] = 'restore_precheck_warnings';
            }
            $rc->execute_plan();
            self::deadline($started, $plan->maxseconds);
            $phase = 'partial';
            $report['checks'] = validation_engine::inspect($report['inventory'], $courseid);
            $report['status'] = validation_engine::classify($report['checks']);
            if ($report['status'] === 'passed' && $report['warnings']) {
                $report['status'] = 'warnings';
            }
        } catch (\Throwable $e) {
            $report['status'] = $phase;
            $report['errorcode'] = report_builder::error_code($e);
            // Never persist raw exception messages: may contain private data or OS paths.
        } finally {
            if ($rc !== null) {
                try {
                    $rc->destroy();
                } catch (\Throwable $ignored) {
                    $report['warnings'][] = 'controller_disposal_failed';
                }
            }
            if ($courseid) {
                try {
                    cleanup_manager::retag($runid);
                } catch (\Throwable $ignored) {
                    $report['warnings'][] = 'ownership_marker_failed';
                }
            }
            if (isset($archivepath) && is_file($archivepath)) {
                @unlink($archivepath);
            }
            // Moodle may already have deleted its own restore temporary directory.
            if (isset($unpackdir) && is_dir($unpackdir)) {
                $expected = realpath($CFG->tempdir . '/backup');
                if ($expected !== false && dirname(realpath($unpackdir)) === $expected &&
                        basename($unpackdir) === $backupid) {
                    fulldelete($unpackdir);
                }
            }
            if ($courseid && !empty($plan->autocleanup)) {
                try {
                    $cleaned = cleanup_manager::clean($runid, true);
                    $report['cleanup'] = $cleaned ? 'deleted' : 'not_verified';
                } catch (\Throwable $cleanupfailure) {
                    $report['cleanup'] = 'failed';
                    $report['warnings'][] = 'cleanup_failed';
                }
            } else if ($courseid) {
                $report['cleanup'] = 'retained';
            }
            $report['durationseconds'] = round(microtime(true) - $started, 3);
            if ($report['status'] === 'passed' && !empty($report['warnings'])) {
                $report['status'] = 'warnings';
            }
            execution_history::finish($runid, $report);
            try {
                if (!in_array($report['status'], ['passed', 'warnings'], true)) {
                    notification_service::failure($runid, $report['status']);
                }
            } catch (\Throwable $ignored) {
                // Notifications must never prevent lock release or modify validated results.
                debugging('Backup drill administrator notification failed.', DEBUG_DEVELOPER);
            } finally {
                $lock->release();
            }
        }
        return $report;
    }

    /**
     * Handle deadline.
     *
     * @param float $start Start time.
     * @param int $limit Allowed runtime.
     */
    private static function deadline(float $start, int $limit): void {
        if (microtime(true) - $start > $limit) {
            throw new \moodle_exception('deadlineexceeded', 'local_backupdrill');
        }
    }

    /**
     * Handle check extracted tree.
     *
     * @param string $path Extracted root.
     * @param int $maxbytes Maximum sum of file sizes.
     */
    private static function check_extracted_tree(string $path, int $maxbytes): void {
        $size = 0;
        $root = realpath($path);
        if ($root === false) {
            throw new \moodle_exception('invalidarchive', 'local_backupdrill');
        }
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path,
            \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $entry) {
            $real = realpath($entry->getPathname());
            if ($entry->isLink() || $real === false ||
                    !str_starts_with($real, $root . DIRECTORY_SEPARATOR)) {
                throw new \moodle_exception('invalidarchive', 'local_backupdrill');
            }
            if ($entry->isFile()) {
                $size += $entry->getSize();
                if ($size > $maxbytes) {
                    throw new \moodle_exception('archivesizelimit', 'local_backupdrill');
                }
            }
        }
    }
}
