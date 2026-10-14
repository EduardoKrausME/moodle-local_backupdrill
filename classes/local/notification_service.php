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
 * Send no archive content, raw exception, student data or external messages.
 */
final class notification_service {
    /**
     * Handle failure.
     *
     * @param int $runid Failed run.
     * @param string $status Machine status code.
     */
    public static function failure(int $runid, string $status): void {
        global $CFG;
        environment_guard::assert_safe();
        $CFG->noemailever = true;
        foreach (get_admins() as $admin) {
            $message = new \core\message\message();
            $message->component = 'local_backupdrill';
            $message->name = 'drillfailure';
            $message->userfrom = \core_user::get_noreply_user();
            $message->userto = $admin;
            $message->subject = get_string('alertsubject', 'local_backupdrill');
            $message->fullmessage = get_string('alertbody', 'local_backupdrill',
                (object) ['runid' => $runid, 'status' => $status]);
            $message->fullmessageformat = FORMAT_PLAIN;
            $message->fullmessagehtml = '';
            $message->smallmessage = $message->fullmessage;
            $message->notification = 1;
            $message->contexturl = (new \moodle_url('/local/backupdrill/index.php'))->out(false);
            $message->contexturlname = get_string('pluginname', 'local_backupdrill');
            message_send($message);
        }
    }
}
