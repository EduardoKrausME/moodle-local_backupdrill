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

defined('MOODLE_INTERNAL') || die();

$string['actions'] = 'Actions';
$string['actual'] = 'Observed';
$string['alertbody'] = 'Backup drill {$a->runid} finished with status {$a->status}. Review the protected lab dashboard.';
$string['alertsubject'] = 'Backup drill failure';
$string['alreadyrunning'] = 'This execution is not pending.';
$string['archivesizelimit'] = 'Extracted archive exceeds size limit.';
$string['attemptsexceeded'] = 'Maximum attempt count exceeded.';
$string['autocleanup'] = 'Delete plugin-owned temporary courses after the test';
$string['back'] = 'Back';
$string['backupdrill:manage'] = 'Manage backup restoration test plans and schedules';
$string['backupdrill:viewreports'] = 'View backup restoration test reports';
$string['check'] = 'Check';
$string['cleanupdenied'] = 'Explicitly authorised cleanup is required.';
$string['cleanupfailed'] = 'Temporary course cleanup failed.';
$string['compare'] = 'Compare';
$string['comparewith'] = 'Compare with run ID';
$string['comparison'] = 'Execution comparison';
$string['current'] = 'Current';
$string['dashboard'] = 'Backup restoration tests';
$string['date'] = 'Date';
$string['deadlineexceeded'] = 'Maximum drill runtime exceeded.';
$string['disable'] = 'Disable schedule';
$string['enable'] = 'Enable schedule';
$string['enabled'] = 'Run automatically on schedule';
$string['errorcode'] = 'Error code';
$string['eventartifact_deleted'] = 'Drill temporary course removed';
$string['eventdrill_completed'] = 'Backup drill completed';
$string['eventplan_created'] = 'Backup drill plan created';
$string['existingfile'] = 'Existing lab MBZ (optional)';
$string['expected'] = 'Expected';
$string['failed'] = 'Failed';
$string['fingerprint'] = 'Enrolled physical environment fingerprint';
$string['history'] = 'Execution history';
$string['intervalhours'] = 'Interval (hours)';
$string['invalidarchive'] = 'Backup file or manifest is invalid.';
$string['invalidplan'] = 'Invalid drill plan or resource limits.';
$string['invalidrun'] = 'Invalid execution record.';
$string['labblocked'] = 'Restoration is blocked: this site is not explicitly enrolled as a network-isolated laboratory.';
$string['labname'] = 'Laboratory name';
$string['labverified'] = 'Laboratory identity verified:';
$string['lockunavailable'] = 'Another drill is using this backup.';
$string['maxseconds'] = 'Maximum runtime (seconds)';
$string['messageprovider:drillfailure'] = 'Backup restoration test failures';
$string['missingplugin'] = 'A required backup component is unavailable in the lab.';
$string['newplan'] = 'New restoration plan';
$string['nocategory'] = 'No category available for temporary courses.';
$string['passed'] = 'Approved';
$string['pending'] = 'Pending';
$string['planname'] = 'Plan name';
$string['plans'] = 'Plans';
$string['pluginname'] = 'Backup Drill';
$string['previous'] = 'Previous';
$string['privacy:metadata:report'] = 'Technical test results which exclude individual user details.';
$string['privacy:metadata:runs'] = 'Run history and validation evidence.';
$string['privacy:metadata:source'] = 'Uploaded backup archives may contain personal data from original courses.';
$string['queued'] = 'Execution {$a} was queued.';
$string['report'] = 'Technical report';
$string['restoreprecheckfailed'] = 'Moodle restore prechecks failed.';
$string['retentiondays'] = 'Keep finished history (days)';
$string['runnow'] = 'Queue test';
$string['saveplan'] = 'Save plan';
$string['settings'] = 'Backup Drill settings';
$string['sourcefile'] = 'Moodle backup (.mbz)';
$string['status'] = 'Status';
$string['taskexecute'] = 'Execute backup restoration test';
$string['taskprune'] = 'Prune expired backup drill history';
$string['taskschedule'] = 'Schedule backup drill runs';
$string['unsafeenvironment'] = 'Unverified environment: restoring or cleaning is prohibited.';
$string['uploadnew'] = 'Upload a new backup instead';
$string['warnings'] = 'With warnings / not verified';
