# Changelog
The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

Exclamation symbols (:exclamation:) note something of importance e.g. breaking changes. Click them to learn more.

## [Unreleased]
### Added
### Changed
### Deprecated
### Removed
### Fixed
### Security

## [0.10.0] - 2021-06-14
### Added
- Rules notice to use Pastebin instead of posting code directly.
### Changed
- Bumped to manager 1.7.0 and core 0.73.
- Various code tweaks, make use of PHP 8.
### Security
- Bumped dependencies.

## [0.9.0] - 2021-03-14
### Changed
- Moved to PHP 8.
- Bump to version 0.71 of core.

## [0.8.0] - 2021-01-01
### Added
- Possibility to set custom Request Client.
### Changed
- Bumped dependencies, use explicit version 0.70.1 of core.
### Fixed
- Only kick users that haven't already been kicked.

## [0.7.0] - 2020-10-04
### Added
- Rules must be agreed to before allowing a user to post in the group. (#43)
### Changed
- Bumped dependencies, use explicit version 0.64.0 of core.
### Security
- Minimum PHP 7.4.

## [0.6.0] - 2020-07-06
### Added
- New `/donate` command, to allow users to donate via Telegram Payments. (#40)
- GitHub authentication to prevent hitting limits. (#41)
### Changed
- Link to the `/rules` command in the welcome message. (#42)

## [0.5.0] - 2019-11-24
### Added
- Description for commands. (#35)
- `/id` command, to help users find their user and chat information. (#36)
### Fixed
- PSR12 compatibility. (#35)
### Security
- Minimum PHP 7.3. (#35)
- Use master branch of core library. (#35)

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
[0.9.0]: https://github.com/php-telegram-bot/support-bot/compare/0.8.0...0.9.0
[0.8.0]: https://github.com/php-telegram-bot/support-bot/compare/0.7.0...0.8.0
[0.7.0]: https://github.com/php-telegram-bot/support-bot/compare/0.6.0...0.7.0
[0.6.0]: https://github.com/php-telegram-bot/support-bot/compare/0.5.0...0.6.0
[0.5.0]: https://github.com/php-telegram-bot/support-bot/compare/0.4.0...0.5.0
[0.4.0]: https://github.com/php-telegram-bot/support-bot/compare/0.3.0...0.4.0
[0.3.0]: https://github.com/php-telegram-bot/support-bot/compare/0.2.0...0.3.0
[0.2.0]: https://github.com/php-telegram-bot/support-bot/compare/0.1.0...0.2.0
