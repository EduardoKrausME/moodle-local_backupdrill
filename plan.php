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

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filelib.php');

require_login();
$context = context_system::instance();
require_capability('local/backupdrill:manage', $context);
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/backupdrill/plan.php'));
$PAGE->set_title(get_string('newplan', 'local_backupdrill'));
$PAGE->set_heading(get_string('pluginname', 'local_backupdrill'));

// Select existing stored Moodle backups on this lab, including built-in course backups.
$existing = [0 => get_string('uploadnew', 'local_backupdrill')];
$sql = 'SELECT id, filename, component, filearea FROM {files}
         WHERE filename LIKE :extension AND (
               (component = :backupcomponent AND filearea IN (:coursearea, :activityarea))
               OR (component = :localcomponent AND filearea = :sourcearea))
         ORDER BY id DESC';
$known = $DB->get_records_sql($sql, [
    'extension' => '%.mbz', 'backupcomponent' => 'backup', 'coursearea' => 'course',
    'activityarea' => 'activity', 'localcomponent' => 'local_backupdrill',
    'sourcearea' => 'source',
], 0, 200);
foreach ($known as $entry) {
    $existing[$entry->id] = $entry->id . ': ' . $entry->filename;
}
$form = new \local_backupdrill\form\plan_form(null, ['existingfiles' => $existing]);
if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/backupdrill/index.php'));
}
if ($data = $form->get_data()) {
    require_sesskey();
    // Import and registration are permitted in the UI, but execution always requires a protected CLI.
    $plan = (object) [
        'name' => trim($data->name), 'fileid' => 0, 'enabled' => (int) $data->enabled,
        'intervalhours' => (int) $data->intervalhours, 'nextrun' => time(),
        'maxseconds' => (int) $data->maxseconds, 'maxbytes' => 536870912,
        'maxattempts' => 2, 'autocleanup' => (int) $data->autocleanup,
        'timecreated' => time(), 'timemodified' => time(),
    ];
    \local_backupdrill\local\drill_configuration::validate($plan);
    $planid = $DB->insert_record('local_backupdrill_plan', $plan);
    try {
        if (!empty($data->existingfileid)) {
            $source = get_file_storage()->get_file_by_id((int) $data->existingfileid);
            if (!$source || !isset($known[$source->get_id()]) || $source->is_directory() ||
                    $source->get_filesize() > $plan->maxbytes) {
                throw new moodle_exception('invalidarchive', 'local_backupdrill');
            }
            get_file_storage()->create_file_from_storedfile([
                'contextid' => $context->id, 'component' => 'local_backupdrill',
                'filearea' => 'source', 'itemid' => $planid, 'filepath' => '/',
                'filename' => $source->get_filename(), 'userid' => $USER->id,
            ], $source);
        } else {
            file_save_draft_area_files((int) $data->backupfile, $context->id,
                'local_backupdrill', 'source', $planid, ['subdirs' => 0, 'maxfiles' => 1]);
        }
        $files = get_file_storage()->get_area_files($context->id,
            'local_backupdrill', 'source', $planid, 'id DESC', false);
        $file = reset($files);
        if (!$file || !preg_match('/\.mbz$/i', $file->get_filename()) ||
                $file->get_filesize() > $plan->maxbytes) {
            throw new moodle_exception('invalidarchive', 'local_backupdrill');
        }
        $plan->id = $planid;
        $plan->fileid = $file->get_id();
        $DB->update_record('local_backupdrill_plan', $plan);
        \local_backupdrill\event\plan_created::create([
            'context' => $context, 'objectid' => $planid,
        ])->trigger();
    } catch (Throwable $e) {
        get_file_storage()->delete_area_files($context->id, 'local_backupdrill', 'source', $planid);
        $DB->delete_records('local_backupdrill_plan', ['id' => $planid]);
        throw $e;
    }
    redirect(new moodle_url('/local/backupdrill/index.php'));
}
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_backupdrill/plan', ['formhtml' => $form->render(),
    'backurl' => (new moodle_url('/local/backupdrill/index.php'))->out(false)]);
echo $OUTPUT->footer();
