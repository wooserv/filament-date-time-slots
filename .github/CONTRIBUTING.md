# Contributing

Thank you for helping improve Filament Date Time Slots. Contributions, bug reports, documentation improvements, and compatibility fixes are welcome.

## Before opening an issue

Please search existing issues and pull requests first. For a bug report, include:

- The package version.
- PHP, Laravel, and Filament versions.
- The operating system and relevant browser details for frontend issues.
- A minimal reproduction or the smallest code sample that demonstrates the problem.
- The expected behavior and the behavior you observed.

Do not include credentials, private customer data, or other sensitive information. Use the private reporting process in [SECURITY.md](SECURITY.md) for security vulnerabilities.

## Local setup

Requirements:

- PHP 8.2 or newer.
- Composer.
- Node.js and npm for frontend asset changes.

Install the dependencies:

```bash
composer install
npm install
```

The package supports Filament 3.2, 4, and 5 with the Laravel versions documented in the [README](../README.md). Use the compatibility workflow for cross-version verification.

## Checks before a pull request

Run the PHP checks:

```bash
composer format -- --test
composer test
```

When changing JavaScript or CSS, rebuild the distributable assets and include the generated files:

```bash
npm run build
```

The generated assets live in `resources/dist`. Do not commit `vendor/`, `node_modules/`, or local PHPUnit/cache files.

## Pull requests

- Keep each pull request focused on one change.
- Add or update tests for behavior changes.
- Preserve compatibility with the supported Filament and Laravel versions.
- Update the README when changing the public API or configuration.
- Add a CHANGELOG entry for user-facing changes.
- Use a clear title and explain the reason for the change.
- Keep commits focused and avoid unrelated formatting changes.

Maintainers may request changes, squash commits, or decline a proposal when it does not fit the package scope. Please keep discussions constructive and respectful.

## Coding standards

PHP code is formatted with Laravel Pint using the repository's `pint.json` configuration. Follow the existing naming, typing, and documentation conventions. Frontend changes should remain compatible with the supported browsers and be formatted consistently with the surrounding source.

## Release notes

Public API changes must follow semantic versioning. Update `CHANGELOG.md` and ensure the compatibility workflow passes before a release is tagged.
