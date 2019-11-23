# Changelog
The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

Exclamation symbols (:exclamation:) note something of importance e.g. breaking changes. Click them to learn more.

## [Unreleased]
### Added
- Description for commands.
- `/id` command, to help users find their user and chat information.
### Changed
### Deprecated
### Removed
### Fixed
- PSR12 compatibility.
### Security
- Minimum PHP 7.3.
- Use master branch of core library.

## [0.4.0] - 2019-08-01
### Changed
- Only log a single welcome message deletion failure. (#34)
### Fixed
- Deprecated system commands are now executed via `GenericmessageCommand`. (#33)

## [0.3.0] - 2019-07-30
### Added
- Code checkers to ensure coding standard. (#30)
- When releasing a new version of the Support Bot, automatically fetch the latest code and install with composer. (#31)
- MySQL cache for GitHub client. (#32)
### Changed
- Bumped Manager to 1.5. (#27)
- Logging is now decoupled with custom Monolog logger. (#28, #29)

## [0.2.0] - 2019-06-01
### Changed
- Bumped Manager to 1.4
### Fixed
- Only post release message when a new release is actually "published". (#25)

## [0.1.0] - 2019-04-15
### Added
- First minor version that contains the basic functionality.
- Simple logging of incoming webhook requests from GitHub and Travis-CI.
- Post welcome messages to PHP Telegram Bot Support group.
- Post release announcements to PHP Telegram Bot Support group. (#17)
- Extended `.env.example` file.

[Unreleased]: https://github.com/php-telegram-bot/support-bot/compare/master...develop
[0.4.0]: https://github.com/php-telegram-bot/support-bot/compare/0.3.0...0.4.0
[0.3.0]: https://github.com/php-telegram-bot/support-bot/compare/0.2.0...0.3.0
[0.2.0]: https://github.com/php-telegram-bot/support-bot/compare/0.1.0...0.2.0
