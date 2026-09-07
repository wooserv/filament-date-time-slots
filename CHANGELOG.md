# Changelog

All notable changes to this package are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

No changes yet.

## [1.1.0] - 2026-09-07

### Added

- Compatibility workflow for Filament 3, 4, and 5 across supported Laravel and PHP versions.
- PHPUnit coverage for component defaults, closures, slot configuration, and date boundaries.
- Configurable calendars, working hours, available slots, blocked slots, and minimum lead time.
- 12-hour and 24-hour display switching without changing the stored datetime value.
- Optional `vertical()` layout mode for the date-time slot picker.

### Changed

- The picker now uses a horizontal layout by default when its own container is at least `48rem` wide.
- Smaller containers, including mobile layouts, automatically use the vertical layout.
- Time slots now have an independent scroll area in both layouts, with a bounded minimum-height layout for vertical mode.

## [1.0.0] - 2026-09-07

### Added

- Initial WooServ release of the Filament Date Time Slots field.

[Unreleased]: https://github.com/wooserv/filament-date-time-slots/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/wooserv/filament-date-time-slots/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/wooserv/filament-date-time-slots/releases/tag/v1.0.0
