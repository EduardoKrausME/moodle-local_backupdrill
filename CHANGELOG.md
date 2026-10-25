# Changelog

This changelog documents only the two latest **published** releases of `local_backupdrill`, verified on October 24, 2026. Each entry is based on the published Git tag compared with its preceding published release tag. Release `1.0.2` occurs in the commit history but has no published GitHub release in the verified list; its changes are included in the cumulative comparison for `1.0.3`.

Changes committed after `1.0.3`, including the October 20–21 `Mustache`, `Boilerplate Headers` and `-core_text` commits, are not included below.

## [1.0.3] - 2026-10-19

### Added
- Added the plugin icon asset at `pix/icon.png`.

### Release automation (historical changes)
- Added release-workflow concurrency settings scoped to the Git ref, intended to queue releases rather than cancel an ongoing release.
- Removed the separate `.github/workflows/validate.yml` workflow. This is a description of the changes already present between the historical release tags, not a modification made while adding this changelog.

### Release metadata
- Updated the plugin version metadata in `version.php` to version `2026101900` and release `1.0.3`.

**Full comparison:** [1.0.1...1.0.3](https://github.com/EduardoKrausME/moodle-local_backupdrill/compare/1.0.1...1.0.3)

## [1.0.1] - 2026-10-14

### Compatibility and code quality
- Reformatted Moodle GPL headers, PHPDoc blocks, namespaces and class documentation across the plugin's checks, events, backup/restore services, scheduled tasks, CLI entry points, privacy provider and tests to address Moodle Code Checker and PHPCS conventions.
- Adjusted Mustache templates to output prepared action/report URLs without HTML escaping in the appropriate URL attributes.
- Updated the Behat dashboard navigation step for test compatibility.
- Added the English and Brazilian Portuguese language strings for the backup-drill management and report-viewing capabilities and the drill-failure message provider.
- Added a developer debugging message when an administrator notification fails during the restore executor's finalization, while retaining lock release in the `finally` block.

### CI and validation (historical changes)
- Updated the existing validation workflow and removed the separate Moodle Plugin CI job from it. The changes were part of the published release comparison; this changelog does not edit workflow files.
- Applied further formatting and test-compatibility corrections to the existing PHP and Behat test suite.

### Release metadata
- Updated the plugin version metadata to `2026101401` and release `1.0.1`.

**Full comparison:** [1.0.0...1.0.1](https://github.com/EduardoKrausME/moodle-local_backupdrill/compare/1.0.0...1.0.1)
