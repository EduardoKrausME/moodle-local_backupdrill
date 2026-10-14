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

define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

[$options, $unrecognised] = cli_get_params([
    'help' => false, 'action' => 'status', 'file' => '', 'name' => '',
    'plan' => 0, 'run' => 0, 'confirm' => '', 'limit' => 20,
], ['h' => 'help']);
if ($options['help'] || $unrecognised) {
    echo "Moodle backup drill (execute only on the enrolled lab)\n";
    echo "  --action=fingerprint        Print local environment fingerprint (no restore)\n";
    echo "  --action=import --file=/path/course.mbz --name=Test\n";
    echo "  --action=queue --plan=ID   Queue a lab restore\n";
    echo "  --action=run --plan=ID     Queue and execute immediately\n";
    echo "  --action=status            Read recent runs\n";
    echo "  --action=clean --run=ID --confirm=RUN:COURSE\n";
    exit($unrecognised ? 1 : 0);
}
$action = $options['action'];
if ($action === 'fingerprint') {
    echo \local_backupdrill\local\environment_guard::fingerprint() . "\n";
    exit(0);
}
if (!in_array($action, ['status', 'import', 'queue', 'run', 'clean'], true)) {
    cli_error('Unknown action.');
}
if ($action !== 'status') {
    \local_backupdrill\local\environment_guard::assert_safe();
}
if ($action === 'import') {
    $filepath = realpath((string) $options['file']);
    if (!$filepath || !is_file($filepath) || !is_readable($filepath) ||
            !preg_match('/\.mbz$/i', $filepath) || filesize($filepath) > 536870912) {
        cli_error('The input must be a readable .mbz smaller than 512 MiB.');
    }
    $plan = (object) [
        'name' => trim($options['name']) ?: basename($filepath), 'fileid' => 0,
        'enabled' => 0, 'intervalhours' => 24, 'nextrun' => 0,
        'maxseconds' => 1800, 'maxbytes' => 536870912, 'maxattempts' => 2,
        'autocleanup' => 0, 'timecreated' => time(), 'timemodified' => time(),
    ];
    \local_backupdrill\local\drill_configuration::validate($plan);
    $planid = $DB->insert_record('local_backupdrill_plan', $plan);
    $filerecord = [
        'contextid' => context_system::instance()->id, 'component' => 'local_backupdrill',
        'filearea' => 'source', 'itemid' => $planid, 'filepath' => '/',
        'filename' => basename($filepath), 'userid' => get_admin()->id,
        'license' => 'allrightsreserved', 'author' => 'Laboratory operator',
    ];
    try {
        $stored = get_file_storage()->create_file_from_pathname($filerecord, $filepath);
        $plan->id = $planid;
        $plan->fileid = $stored->get_id();
        $DB->update_record('local_backupdrill_plan', $plan);
        echo 'Plan ' . $planid . " imported.\n";
    } catch (Throwable $e) {
        get_file_storage()->delete_area_files(context_system::instance()->id,
            'local_backupdrill', 'source', $planid);
        $DB->delete_records('local_backupdrill_plan', ['id' => $planid]);
        cli_error('Import failed.');
    }
} else if ($action === 'queue' || $action === 'run') {
    $planid = (int) $options['plan'];
    if ($planid <= 0) {
        cli_error('Specify --plan=ID.');
    }
    $runid = \local_backupdrill\local\execution_history::queue($planid, $action === 'queue');
    echo 'Run ' . $runid . " queued.\n";
    if ($action === 'run') {
        $report = \local_backupdrill\local\restore_executor::execute($runid);
        echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        exit($report['status'] === 'passed' || $report['status'] === 'warnings' ? 0 : 2);
    }
} else if ($action === 'clean') {
    $runid = (int) $options['run'];
    $artifact = $DB->get_record('local_backupdrill_art', ['runid' => $runid], '*', MUST_EXIST);
    if ($options['confirm'] !== $runid . ':' . $artifact->courseid) {
        cli_error('Explicit --confirm=RUN:COURSE is required.');
    }
    echo \local_backupdrill\local\cleanup_manager::clean($runid, true) ? "Deleted.\n" : "Not owned/already absent.\n";
} else if ($action === 'status') {
    $max = max(1, min(100, (int) $options['limit']));
    foreach ($DB->get_records('local_backupdrill_run', null, 'id DESC', '*', 0, $max) as $run) {
        echo $run->id . "\t" . $run->planid . "\t" . $run->status . "\t" . $run->errorcode . "\n";
    }
}
