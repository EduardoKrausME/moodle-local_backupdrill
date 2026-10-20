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

namespace local_backupdrill\local;

/**
 * Portable, bounded diagnostic output.
 */
final class report_builder {
    /**
     * Handle start.
     *
     * @param int $runid ID.
     * @return array Base report.
     */
    public static function start(int $runid): array {
        return ['schema' => 1, 'runid' => $runid, 'status' => 'pending',
            'errorcode' => '', 'inventory' => [], 'checks' => [], 'warnings' => [],
            'durationseconds' => 0, 'created' => time()];
    }

    /**
     * Handle error code.
     *
     * @param \Throwable $exception Error whose raw message may include private data.
     * @return string Safe code.
     */
    public static function error_code(\Throwable $exception): string {
        if ($exception instanceof \moodle_exception) {
            return preg_replace('/[^a-z0-9_]/', '', strtolower($exception->errorcode)) ?: 'moodle_error';
        }
        return 'runtime_error';
    }

    /**
     * Handle csv.
     *
     * @param array $report Report.
     * @return string CSV document.
     */
    public static function csv(array $report): string {
        $stream = fopen('php://temp', 'w+');
        fputcsv($stream, ['runid', 'status', 'check', 'result', 'expected', 'actual'], ',', '"', '');
        foreach ($report['checks'] ?? [] as $check) {
            $checkid = (string) ($check['id'] ?? '');
            if ($checkid !== '' && in_array($checkid[0], ['=', '+', '-', '@'], true)) {
                $checkid = "'" . $checkid;
            }
            fputcsv($stream, [(int) $report['runid'], $report['status'],
                $checkid, $check['status'] ?? '',
                json_encode($check['expected'] ?? null), json_encode($check['actual'] ?? null)], ',', '"', '');
        }
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);
        return $csv;
    }
}
