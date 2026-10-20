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

require_once(__DIR__ . '/../../config.php');
require_login();
$context = context_system::instance();
require_capability('local/backupdrill:viewreports', $context);
$id = required_param('id', PARAM_INT);
$format = optional_param('format', 'html', PARAM_ALPHA);
$compareid = optional_param('compare', 0, PARAM_INT);
$run = $DB->get_record('local_backupdrill_run', ['id' => $id], '*', MUST_EXIST);
$report = json_decode((string) $run->reportjson, true);
$report = is_array($report) ? $report : ['runid' => $id, 'status' => $run->status, 'checks' => []];
if ($format === 'json' || $format === 'csv') {
    // No personal data or raw backup content is included in either export.
    $filename = 'backupdrill-' . $id . '.' . $format;
    header('Content-Type: ' . ($format === 'json' ? 'application/json' : 'text/csv') . '; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('X-Content-Type-Options: nosniff');
    echo $format === 'json' ? json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        : \local_backupdrill\local\report_builder::csv($report);
    exit;
}
if ($format !== 'html') {
    throw new moodle_exception('invalidparameter');
}
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/backupdrill/report.php', ['id' => $id]));
$PAGE->set_title(get_string('report', 'local_backupdrill'));
$PAGE->set_heading(get_string('pluginname', 'local_backupdrill'));
$checks = [];
foreach ($report['checks'] as $check) {
    $checks[] = ['name' => $check['id'] ?? '', 'status' => $check['status'] ?? 'not_verified',
        'expected' => json_encode($check['expected'] ?? null),
        'actual' => json_encode($check['actual'] ?? null)];
}
$comparison = [];
if ($compareid > 0 && $compareid !== $id) {
    $previous = $DB->get_record('local_backupdrill_run', ['id' => $compareid, 'planid' => $run->planid],
        '*', MUST_EXIST);
    $oldreport = json_decode((string) $previous->reportjson, true);
    $oldchecks = [];
    foreach (($oldreport['checks'] ?? []) as $check) {
        $oldchecks[$check['id']] = $check;
    }
    foreach ($report['checks'] as $check) {
        $idkey = $check['id'];
        $prior = $oldchecks[$idkey] ?? ['status' => 'not_verified', 'actual' => null];
        $comparison[] = ['name' => $idkey,
            'beforestatus' => $prior['status'], 'afterstatus' => $check['status'],
            'beforeactual' => json_encode($prior['actual'] ?? null),
            'afteractual' => json_encode($check['actual'] ?? null)];
    }
}
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_backupdrill/report', [
    'id' => $id, 'status' => $report['status'], 'checks' => $checks,
    'comparison' => $comparison, 'hascomparison' => !empty($comparison), 'compareid' => $compareid,
    'currenturl' => (new moodle_url('/local/backupdrill/report.php'))->out(false),
    'errorcode' => $report['errorcode'] ?? '',
    'jsonurl' => (new moodle_url('/local/backupdrill/report.php', ['id' => $id, 'format' => 'json']))->out(false),
    'csvurl' => (new moodle_url('/local/backupdrill/report.php', ['id' => $id, 'format' => 'csv']))->out(false),
    'backurl' => (new moodle_url('/local/backupdrill/index.php'))->out(false),
]);
echo $OUTPUT->footer();
