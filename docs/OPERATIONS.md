# Lab operator runbook

## Isolation prerequisites

Before any restore, require independent database credentials and `moodledata`, no production secrets, disabled external integrations and network egress denied by a real firewall/container networking policy. Schedule restores on a disposable lab with filesystem quotas and a dedicated hidden category whose ID number matches `backupdrill_<labid>`. Enrolling the fingerprint is an explicit human decision, **not evidence** of network isolation. Always configure SMTP and external integrations defensively as if untrusted Moodle backup data may trigger hooks.

## Run

Install plugin, configure `config.php`, enroll fingerprint, create/import `.mbz` on **the laboratory Moodle**, and queue a plan. Moodle cron executes the ad-hoc job. To put a hard time cap around a manual test, run `timeout --signal=TERM --kill-after=30s 35m php local/backupdrill/cli/backupdrill.php --action=run --plan=1`, or use an equivalent OS-level orchestration limit. Limit disk quota separately: a highly compressed backup can explode in size before post-extraction guards run. Leave source backups in a controlled private storage location.

## Incidents

If Moodle cron crashes, treat a `running` record as interrupted and **do not re-run the same record**. Inspect the recorded `courseid` and ownership registry before starting a fresh run. Never delete a course by guessing its ID or name. Do not use SQL rollback to undo a restore; restored Moodle data spans database/files/events. Keep the lab isolated until the incident is inspected.

For a previously created temporary course, inspect `local_backupdrill_art` (one record per run) and invoke CLI `--action=clean --run=ID --confirm=ID:COURSEID`. A mismatch in plugin provenance is a refusal, not permission to force-delete anything. Artifacts left after interruption may need supervised cleanup using core Moodle tools after independent confirmation, never an unattended glob.

## Known limitations

- No external access/remote production API and no automatic archive retrieval from production.
- Files and question categories are counted as observations but cannot yet be declared equal, so end-to-end runs retain an explicit `not_verified` state and the overall result is `warnings` unless another check fails.
- Course settings and cross-module/availability references are not fully validated.
- Hard execution and disk cutoffs require process and filesystem controls outside PHP.
- Scheduled history pruning does not remove stored source `.mbz` files or uncleaned courses.
- Reporting compares technical checks. Restore metadata may be richer than this initial importer handles.
