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
 * Inventory uses structured manifests instead of assuming every MBZ is a ZIP.
 */
final class backup_inventory {
    /**
     * Handle inspect.
     *
     * @param string $path Local extracted Moodle backup directory.
     * @return array Inventory.
     */
    public static function inspect(string $path): array {
        $xml = self::read_xml($path . '/moodle_backup.xml', true);
        $sections = $xml->xpath('//contents/sections/section') ?: [];
        $activities = $xml->xpath('//contents/activities/activity') ?: [];
        $modules = [];
        foreach ($activities as $activity) {
            $type = trim((string) $activity->modulename);
            if ($type !== '' && !preg_match('/^[a-z][a-z0-9_]{0,99}$/D', $type)) {
                throw new \moodle_exception('invalidarchive', 'local_backupdrill');
            }
            if ($type !== '') {
                $modules[$type] = ($modules[$type] ?? 0) + 1;
            }
        }
        $filecount = null;
        $checked = 0;
        $corrupt = 0;
        $files = self::read_xml($path . '/files.xml');
        if ($files !== null) {
            $entries = $files->xpath('/files/file') ?: [];
            $filecount = count($entries);
            if ($filecount > 50000) {
                throw new \moodle_exception('archivesizelimit', 'local_backupdrill');
            }
            foreach ($entries as $entry) {
                $hash = (string) $entry->contenthash;
                $filename = (string) $entry->filename;
                if ($filename === '.' || $hash === '') {
                    continue;
                }
                $checked++;
                if (!preg_match('/^[a-f0-9]{40}$/D', $hash)) {
                    $corrupt++;
                    continue;
                }
                $payload = $path . '/files/' . substr($hash, 0, 2) . '/' . $hash;
                if (!is_file($payload) || sha1_file($payload) !== $hash) {
                    $corrupt++;
                }
            }
        }
        $questioncount = null;
        $qtypes = [];
        $questionxml = self::read_xml($path . '/questions.xml');
        if ($questionxml !== null) {
            $questioncount = count($questionxml->xpath('//question_category') ?: []);
            foreach (($questionxml->xpath('//question/qtype | //question/type') ?: []) as $type) {
                $name = trim((string) $type);
                if ($name !== '' && preg_match('/^[a-z][a-z0-9_]{0,99}$/D', $name)) {
                    $qtypes[$name] = true;
                }
            }
        }
        return [
            'format' => (string) ($xml->information->format ?? ''),
            'moodleversion' => (string) ($xml->information->moodle_version ?? ''),
            'sections' => count($sections),
            'modules' => $modules,
            'files' => $filecount,
            'filepayloadchecked' => $checked,
            'filepayloadcorrupt' => $corrupt,
            'questioncategories' => $questioncount,
            'questiontypes' => array_keys($qtypes),
        ];
    }

    /**
     * Handle read xml.
     *
     * @param string $name XML file path.
     * @param bool $required Whether missing or invalid XML is fatal.
     * @return \SimpleXMLElement|null Parsed XML if present.
     */
    private static function read_xml(string $name, bool $required = false): ?\SimpleXMLElement {
        if (!is_file($name)) {
            if ($required) {
                throw new \moodle_exception('invalidarchive', 'local_backupdrill');
            }
            return null;
        }
        if (filesize($name) > 20971520) {
            throw new \moodle_exception('invalidarchive', 'local_backupdrill');
        }
        $previous = libxml_use_internal_errors(true);
        try {
            $result = simplexml_load_file($name, 'SimpleXMLElement', LIBXML_NONET);
            if ($result === false) {
                throw new \moodle_exception('invalidarchive', 'local_backupdrill');
            }
            return $result;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
