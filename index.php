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

require_once(__DIR__ . '/../../config.php');
require_login();
$context = context_system::instance();
require_capability('local/backupdrill:viewreports', $context);
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/backupdrill/index.php'));
$PAGE->set_title(get_string('pluginname', 'local_backupdrill'));
$PAGE->set_heading(get_string('pluginname', 'local_backupdrill'));
$canmanage = has_capability('local/backupdrill:manage', $context);
if (data_submitted() && in_array(optional_param('action', '', PARAM_ALPHA), ['queue', 'toggle'], true)) {
    require_sesskey();
    require_capability('local/backupdrill:manage', $context);
    $planid = required_param('planid', PARAM_INT);
    if (required_param('action', PARAM_ALPHA) === 'toggle') {
        $plan = $DB->get_record('local_backupdrill_plan', ['id' => $planid], '*', MUST_EXIST);
        $plan->enabled = $plan->enabled ? 0 : 1;
        $plan->nextrun = time();
        $plan->timemodified = time();
        $DB->update_record('local_backupdrill_plan', $plan);
        redirect(new moodle_url('/local/backupdrill/index.php'));
    }
    $runid = \local_backupdrill\local\execution_history::queue($planid);
    redirect(new moodle_url('/local/backupdrill/index.php'),
        get_string('queued', 'local_backupdrill', $runid));
}
$plans = $DB->get_records('local_backupdrill_plan', null, 'id DESC', '*', 0, 100);
$planrows = [];
foreach ($plans as $plan) {
    $planrows[] = ['id' => (int) $plan->id, 'name' => format_string($plan->name),
        'enabled' => (bool) $plan->enabled, 'canmanage' => $canmanage,
        'last' => userdate($plan->nextrun), 'interval' => (int) $plan->intervalhours];
}
$runs = $DB->get_records('local_backupdrill_run', null, 'id DESC', '*', 0, 200);
$runrows = [];
$summary = ['pending' => 0, 'passed' => 0, 'failed' => 0, 'warnings' => 0];
foreach ($runs as $run) {
    if ($run->status === 'pending' || $run->status === 'running') {
        $summary['pending']++;
    } else if ($run->status === 'passed') {
        $summary['passed']++;
    } else if ($run->status === 'warnings') {
        $summary['warnings']++;
    } else {
        $summary['failed']++;
    }
    $reporturl = new moodle_url('/local/backupdrill/report.php', ['id' => $run->id]);
    $runrows[] = ['id' => (int) $run->id, 'planid' => (int) $run->planid,
        'status' => $run->status, 'created' => userdate($run->timecreated),
        'duration' => $run->finished && $run->started ? $run->finished - $run->started : 0,
        'reporturl' => $reporturl->out(false)];
}
try {
    \local_backupdrill\local\environment_guard::assert_safe(false);
    $labready = true;
} catch (moodle_exception $e) {
    $labready = false;
}
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_backupdrill/dashboard', [
    'labready' => $labready, 'labname' => s((string) get_config('local_backupdrill', 'labname')),
    'canmanage' => $canmanage, 'plans' => $planrows, 'runs' => $runrows,
    'summary' => $summary, 'sesskey' => sesskey(),
    'newplanurl' => (new moodle_url('/local/backupdrill/plan.php'))->out(false),
    'actionurl' => (new moodle_url('/local/backupdrill/index.php'))->out(false),
]);
echo $OUTPUT->footer();
