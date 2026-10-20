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

namespace local_backupdrill\privacy;

use core_privacy\local\metadata\collection;

/**
 * Site-owned technical records; original MBZ files may carry personal information.
 */
final class provider implements \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider {
    /**
     * Handle get metadata.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_subsystem_link('core_files', [], 'privacy:metadata:source');
        $collection->add_database_table('local_backupdrill_run', [
            'reportjson' => 'privacy:metadata:report',
        ], 'privacy:metadata:runs');
        return $collection;
    }

    /**
     * Handle get contexts for userid.
     */
    public static function get_contexts_for_userid(int $userid): \core_privacy\local\request\contextlist {
        $list = new \core_privacy\local\request\contextlist();
        $sql = 'SELECT DISTINCT f.contextid FROM {files} f
                 WHERE f.component = :component AND f.filearea = :filearea AND f.userid = :userid';
        $list->add_from_sql($sql, ['component' => 'local_backupdrill',
            'filearea' => 'source', 'userid' => $userid]);
        return $list;
    }

    /**
     * Handle export user data.
     */
    public static function export_user_data(\core_privacy\local\request\approved_contextlist $contextlist): void {
        foreach ($contextlist->get_contexts() as $context) {
            \core_privacy\local\request\writer::with_context($context)->export_area_files(
                [], 'local_backupdrill', 'source');
        }
    }

    /**
     * Handle delete data for all users in context.
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        // Source archives may contain data about other people; never destroy unrelated backups here.
    }

    /**
     * Handle delete data for user.
     */
    public static function delete_data_for_user(\core_privacy\local\request\approved_contextlist $contextlist): void {
        // Preserve audit integrity and unrelated users' restored content; deletion is lab-admin governed.
    }
}
