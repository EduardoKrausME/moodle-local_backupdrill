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
 * Defense in depth for explicitly enrolled laboratory sites.
 *
 * @package    local_backupdrill
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_backupdrill\local;

defined('MOODLE_INTERNAL') || die();

/** No restore, schedule or cleanup happens without these fences. */
final class environment_guard {
    /** @return string Stable physical Moodle identity, used for explicit lab enrollment. */
    public static function fingerprint(): string {
        global $CFG;
        $realdata = realpath($CFG->dataroot);
        if ($realdata === false) {
            throw new \moodle_exception('unsafeenvironment', 'local_backupdrill');
        }
        return hash('sha256', implode("\n", [
            (string) $CFG->wwwroot, (string) $CFG->dbtype, (string) $CFG->dbhost,
            (string) $CFG->dbname, (string) $CFG->dbuser, $realdata, (string) SITEID,
        ]));
    }

    /** @param bool $requirecli Require real CLI for any operation with side effects. */
    public static function assert_safe(bool $requirecli = true): void {
        global $CFG, $DB;
        if (($requirecli && PHP_SAPI !== 'cli') ||
                !defined('LOCAL_BACKUPDRILL_LAB_ENABLED') || LOCAL_BACKUPDRILL_LAB_ENABLED !== true ||
                !defined('LOCAL_BACKUPDRILL_NETWORK_DENIED') || LOCAL_BACKUPDRILL_NETWORK_DENIED !== true ||
                empty($CFG->local_backupdrill_labid) ||
                empty($CFG->local_backupdrill_categoryid) ||
                empty($CFG->local_backupdrill_production_db) ||
                empty($CFG->local_backupdrill_production_dataroot)) {
            throw new \moodle_exception('unsafeenvironment', 'local_backupdrill');
        }
        $productiondb = (string) $CFG->local_backupdrill_production_db;
        $productiondata = (string) $CFG->local_backupdrill_production_dataroot;
        if (hash_equals($productiondb, (string) $CFG->dbname) ||
                hash_equals($productiondata, (string) realpath($CFG->dataroot)) ||
                !preg_match('/^[a-z0-9_-]{3,64}$/D', $CFG->local_backupdrill_labid)) {
            throw new \moodle_exception('unsafeenvironment', 'local_backupdrill');
        }
        $category = $DB->get_record('course_categories', ['id' => (int) $CFG->local_backupdrill_categoryid],
            'id,visible,idnumber');
        if (!$category || $category->visible ||
                $category->idnumber !== 'backupdrill_' . $CFG->local_backupdrill_labid) {
            throw new \moodle_exception('unsafeenvironment', 'local_backupdrill');
        }
        $configured = (string) get_config('local_backupdrill', 'fingerprint');
        if (strlen($configured) !== 64 || !hash_equals($configured, self::fingerprint())) {
            throw new \moodle_exception('unsafeenvironment', 'local_backupdrill');
        }
        // Moodle mail is disabled in this process; network egress must ALSO be blocked at OS level.
        if ($requirecli) {
            $CFG->noemailever = true;
        }
    }
}
