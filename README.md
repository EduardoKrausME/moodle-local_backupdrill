# Backup Drill (`local_backupdrill`) · 1.0.0

Administrative tool to **test restoration of Moodle `.mbz` archives in a dedicated laboratory**. It does not call production remotely. An archive that can be unpacked is **not** deemed restored: the lab creates a disposable course with Moodle's `restore_controller`, runs prechecks and the real execution plan, then compares the restored course against the manifest.

**Supported:** Moodle 4.5 LTS onward and 5.x, subject to integration verification for each minor release. Source files and diagnostic records never go to AI services.

**Safety boundary:** This plugin is intended to be **installed and executed only in the test installation**. Merely naming an environment `hml`/`staging` is not protection. This source cannot independently prove the absence of network egress or production secrets; enforce those controls at infrastructure level. `LOCAL_BACKUPDRILL_NETWORK_DENIED` is an *operator assertion*, not a firewall.

## Before enabling the lab

1. Provision **separate database**, **separate moodledata** and separate credentials; do not copy production API tokens, outgoing SMTP credentials, payment or SSO secrets.
2. Block **all outbound network traffic** at the container/VM firewall, including mail, webhooks, LTI, payment gateways and scheduled integrations. Disable outgoing email, web services, messaging processors and nonessential scheduled tasks in the lab; `noemailever` is additionally set while a drill runs.
3. Create a **hidden category** specifically for disposable drill courses, and give it the ID number `backupdrill_qa-isolated-01` (replace the suffix with the configured lab ID). No other course should be placed there.
4. Install the plugin under `local/backupdrill`, upgrade Moodle and keep the administrator capabilities restricted (neither is assigned to ordinary archetypes).
5. In the **test installation's** `config.php`, before Moodle bootstrap, add the constants and explicit metadata shown below; these values must NOT be copied to production:

```php
define('LOCAL_BACKUPDRILL_LAB_ENABLED', true);
define('LOCAL_BACKUPDRILL_NETWORK_DENIED', true); // Operator attests firewall egress is blocked.
$CFG->local_backupdrill_labid = 'qa-isolated-01';
$CFG->local_backupdrill_categoryid = 123; // Hidden, dedicated, idnumber=backupdrill_qa-isolated-01
$CFG->local_backupdrill_production_db = 'prod_moodle_database_name';
$CFG->local_backupdrill_production_dataroot = '/path/to/production/moodledata';
```

6. On the lab's CLI, run `php local/backupdrill/cli/backupdrill.php --action=fingerprint`. Copy the SHA-256 value to Site administration > Plugins > Local plugins > Backup Drill settings > Fingerprint; configure a human-readable lab name. The fingerprint ties the approval to `wwwroot`, DB type/host/name/user, real `moodledata` directory and `SITEID`. Changing any of these invalidates the permit.
7. Execute `php admin/cli/cron.php` in the lab; use a **separate worker / process-level timeout**, limited CPU and memory, disk quotas and network sandbox. Moodle's restore APIs can perform blocking operations, so checking elapsed wall time *between* stages is not a hard operating-system timeout.

**Do not add these constants to a production installation.** The guard is fail-closed for any restoration/cleanup attempt on an unregistered site, including cron, and restores are CLI-only. A compromised operator who configures false production identifiers can defeat declarative guards; this is why infrastructure isolation is mandatory.

## Example workflow

```
php local/backupdrill/cli/backupdrill.php --action=import --file=/secure/inbox/course.mbz --name=Quarterly-demo
php local/backupdrill/cli/backupdrill.php --action=run --plan=1
php local/backupdrill/cli/backupdrill.php --action=status
php local/backupdrill/cli/backupdrill.php --action=clean --run=1 --confirm=1:123
```

Or create a plan from the administrative interface using Moodle's File API picker. Recurrence runs through a scheduled task, queues `execute_run` ad-hoc tasks, and uses the content hash in the Lock API to prevent simultaneous restores of the same archive. By default, the temporary course is retained until an operator invokes explicit guarded cleanup; automatic cleanup may be enabled per plan. The uploaded `.mbz` stays in the File API and is never exported publicly.

## States and interpretation

| State | Meaning |
| --- | --- |
| `pending` / `running` | Job queued or currently executing. |
| `read_failed` | Missing/oversized/unreadable archive or invalid manifest. |
| `restore_failed` | Restore precheck or execution failed, regardless of whether the temp course exists. |
| `partial` | Restore completed, but at least one essential validation found a missing component. |
| `warnings` | Restore completed with nonfatal warnings or evidence not verified. |
| `passed` | All participating checks passed and no warnings (not expected when mandatory checks are not yet implemented). |

**Version 1.0.0 verification limits:** Sections and modules are counted against the backup manifest; plugin availability is checked and Moodle's availability JSON is verified for syntax. Source archive file count and question category counts are reported as *not verified* because the restore transforms their contexts and files, and byte-by-byte hashes, full completion semantics, link/reference resolution, question bank parity and course-level configuration are not fully checked. This means successful restores with those gaps report **`warnings`, never `passed`**. The unimplemented parts are not represented as successes. Plugin version *compatibility* beyond installed/absent is also not proven.

When a job fails after creating a course, the course may remain for inspection, particularly after interrupted CLI tasks; CLI cleanup verifies the exact ownership record, generated shortname and marker before deletion. Never manually rename temporary courses or rewrite their owned metadata if they are to be deleted automatically.

## Private information and retention

Archives may contain student/user records. Only authorized site administrators may import, inspect and download **technical** reports; direct `.mbz` download is not exposed by this plugin. JSON/CSV reports include counts, statuses and sanitized error codes, not original titles, names, IDs from the source site, files or raw stack traces. Retention defaults to 90 days (minimum 7), with scheduled pruning only for finished runs that have no retained temporary course. Source archives remain until an authorized operator deletes them from the lab; an archive may therefore outlive its report. Moodle Privacy API declares File API storage and technical report records; removal of original user data from a stored course backup requires operator-controlled archive lifecycle handling, not byte-level redaction.

## Development / verification

1. Run `python3 /path/moodle-plugin-validate/.chatgpt/run.py /absolute/path/to/backupdrill` then `python3 /path/moodle-plugin-validate/.chatgpt/run.py --check /absolute/path/to/backupdrill` after cloning [moodle-plugin-validate](https://github.com/EduardoKrausME/moodle-plugin-validate). Review normalization diffs.
2. Run `find local/backupdrill -name '*.php' -exec php -l {} \;`, `moodle-plugin-ci phplint`, `moodle-plugin-ci phpcs`, `moodle-plugin-ci codechecker`, `moodle-plugin-ci phpunit --fail-on-warning` and Behat if scenarios are supplied.
3. Synthetic tests exercise manifest, limit, safety and evidence logic. A full disposable Moodle PHPUnit database is required for `restore_integration_test.php`; run with `BACKUPDRILL_RUN_INTEGRATION=1`. It creates a fictional course with assignment and quiz, backs it up and restores it using Moodle's native APIs, but does **not** emulate a production clone. Full archive parity, file and bank testing is not part of this first fixture.

See [docs/OPERATIONS.md](docs/OPERATIONS.md) for failure response and cleanup safeguards.

GPL-3.0-or-later · 2026 Eduardo Kraus
