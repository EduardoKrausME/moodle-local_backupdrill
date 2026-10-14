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

namespace local_backupdrill\local;

/**
 * No numeric course ID alone is sufficient proof of ownership.
 */
final class cleanup_manager {
    /**
     * Handle register.
     *
     * @param int $runid The run creating the disposable course.
     * @param int $courseid Its course ID.
     * @param string $shortname Generated short name.
     * @return string Permanent random token.
     */
    public static function register(int $runid, int $courseid, string $shortname): string {
        global $DB;
        $token = bin2hex(random_bytes(32));
        $DB->insert_record('local_backupdrill_art', (object) [
            'runid' => $runid, 'courseid' => $courseid, 'token' => $token,
            'shortname' => $shortname, 'deletedat' => 0, 'timecreated' => time(),
        ]);
        $DB->set_field('course', 'idnumber', 'backupdrill:' . substr($token, 0, 24), ['id' => $courseid]);
        return $token;
    }

    /**
     * Handle clean.
     *
     * @param int $runid Owned execution.
     * @param bool $authorized Explicit cleanup instruction.
     * @return bool Whether a course was deleted.
     */
    public static function clean(int $runid, bool $authorized): bool {
        global $DB, $CFG;
        environment_guard::assert_safe();
        if (!$authorized) {
            throw new \moodle_exception('cleanupdenied', 'local_backupdrill');
        }
        $artifact = $DB->get_record('local_backupdrill_art', ['runid' => $runid, 'deletedat' => 0]);
        if (!$artifact || !$DB->record_exists('course', ['id' => $artifact->courseid,
                'shortname' => $artifact->shortname,
                'category' => (int) $CFG->local_backupdrill_categoryid, 'visible' => 0]) ||
                !preg_match('/^BDRL-[0-9]+-[a-f0-9]{12}$/D', $artifact->shortname) ||
                !hash_equals('backupdrill:' . substr($artifact->token, 0, 24),
                    (string) $DB->get_field('course', 'idnumber', ['id' => $artifact->courseid]))) {
            // Missing or altered resources are not automatically deletable.
            return false;
        }
        require_once($CFG->dirroot . '/course/lib.php');
        if (!delete_course((int) $artifact->courseid, false)) {
            throw new \moodle_exception('cleanupfailed', 'local_backupdrill');
        }
        $artifact->deletedat = time();
        $DB->update_record('local_backupdrill_art', $artifact);
        \local_backupdrill\event\artifact_deleted::create([
            'context' => \context_system::instance(), 'objectid' => $artifact->id,
        ])->trigger();
        return true;
    }

    /**
     * Reinstate the ownership marker when the restore overwrites destination course metadata.
     *
     * @param int $runid Run ID.
     * @return void
     */
    public static function retag(int $runid): void {
        global $DB;
        environment_guard::assert_safe();
        $artifact = $DB->get_record('local_backupdrill_art', ['runid' => $runid, 'deletedat' => 0]);
        if (!$artifact || !$DB->record_exists('course', ['id' => $artifact->courseid])) {
            return;
        }
        $DB->update_record('course', (object) [
            'id' => $artifact->courseid, 'shortname' => $artifact->shortname,
            'idnumber' => 'backupdrill:' . substr($artifact->token, 0, 24),
            'visible' => 0,
        ]);
    }
}
