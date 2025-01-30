# Changelog

All notable changes to `laravel-backup-server` will be documented in this file

## 4.0.1 - 2025-01-30

- add license

**Full Changelog**: https://github.com/spatie/laravel-backup-server/compare/4.0.0...4.0.1

## 4.0.0 - 2025-01-27

### What's Changed

* v4: Pause notifications by @Nielsvanpach in https://github.com/spatie/laravel-backup-server/pull/78
* V4 release by @Nielsvanpach in https://github.com/spatie/laravel-backup-server/pull/77
* Change internal links from /v1/ to /v3/ by @juukie in https://github.com/spatie/laravel-backup-server/pull/73

### New Contributors

* @juukie made their first contribution in https://github.com/spatie/laravel-backup-server/pull/73

**Full Changelog**: https://github.com/spatie/laravel-backup-server/compare/3.3.0...4.0.0

## 3.3.0 - 2024-06-12

### What's Changed

* introduce Dependabot by @Nielsvanpach in https://github.com/spatie/laravel-backup-server/pull/63
* Update github actions + run Pint by @Nielsvanpach in https://github.com/spatie/laravel-backup-server/pull/67
* Skeleton changes by @Nielsvanpach in https://github.com/spatie/laravel-backup-server/pull/68
* Migrate to PestPHP by @Nielsvanpach in https://github.com/spatie/laravel-backup-server/pull/69

**Full Changelog**: https://github.com/spatie/laravel-backup-server/compare/3.2.0...3.3.0

## 3.2.0 - 2024-03-15

### What's Changed

* Support Laravel 11
* Bump follow-redirects from 1.14.8 to 1.15.4 by @dependabot in https://github.com/spatie/laravel-backup-server/pull/55
* Bump browserify-sign from 4.0.4 to 4.2.2 by @dependabot in https://github.com/spatie/laravel-backup-server/pull/54
* Bump @babel/traverse from 7.8.3 to 7.23.2 by @dependabot in https://github.com/spatie/laravel-backup-server/pull/53
* Fix some problems by @mozex in https://github.com/spatie/laravel-backup-server/pull/58
* Bump follow-redirects from 1.15.4 to 1.15.6 by @dependabot in https://github.com/spatie/laravel-backup-server/pull/60
* Bump ip from 1.1.5 to 1.1.9 by @dependabot in https://github.com/spatie/laravel-backup-server/pull/59

### New Contributors

* @mozex made their first contribution in https://github.com/spatie/laravel-backup-server/pull/58

**Full Changelog**: https://github.com/spatie/laravel-backup-server/compare/3.1.3...3.2.0

## 3.1.3 - 2024-03-15

### What's Changed

* Bump semver from 5.7.1 to 5.7.2 by @dependabot in https://github.com/spatie/laravel-backup-server/pull/52
* Fix empty path error for Backup model by @kostamilorava in https://github.com/spatie/laravel-backup-server/pull/57

### New Contributors

* @kostamilorava made their first contribution in https://github.com/spatie/laravel-backup-server/pull/57

**Full Changelog**: https://github.com/spatie/laravel-backup-server/compare/3.1.2...3.1.3

## 3.1.2 - 2023-04-07

- support Laravel 10

## 3.1.1 - 2023-02-24

### What's Changed

- Bump json5 from 1.0.1 to 1.0.2 by @dependabot in https://github.com/spatie/laravel-backup-server/pull/44
- Bump express from 4.17.1 to 4.18.2 by @dependabot in https://github.com/spatie/laravel-backup-server/pull/43
- Bump decode-uri-component from 0.2.0 to 0.2.2 by @dependabot in https://github.com/spatie/laravel-backup-server/pull/42
- Bump minimatch from 3.0.4 to 3.1.2 by @dependabot in https://github.com/spatie/laravel-backup-server/pull/47
- Bump async from 2.6.3 to 2.6.4 by @dependabot in https://github.com/spatie/laravel-backup-server/pull/40
- Bump eventsource from 1.0.7 to 1.1.1 by @dependabot in https://github.com/spatie/laravel-backup-server/pull/39
- Allow timeout for backup collection size calculation to be configurable by @Harrisonbro in https://github.com/spatie/laravel-backup-server/pull/48

**Full Changelog**: https://github.com/spatie/laravel-backup-server/compare/3.1.0...3.1.1

## 3.1.0 - 2023-02-19

### What's Changed

- Allow timeout for backup size calculation to be configurable by @Harrisonbro in https://github.com/spatie/laravel-backup-server/pull/45

### New Contributors

- @Harrisonbro made their first contribution in https://github.com/spatie/laravel-backup-server/pull/45

**Full Changelog**: https://github.com/spatie/laravel-backup-server/compare/3.0.0...3.1.0

## 3.0.0 - 2022-04-01

- add support for Laravel 9

## 2.0.1 - 2021-07-26

- increase disk usage command timeout (#21)

## 2.0.0 - 2020-12-02

- use a cron expression to determine backup time
- drop support for PHP 7

## 1.0.5 - 2020-10-24

- remove dead code

## 1.0.4 - 2020-10-23

- remove `viewMailcoach` authorization gate
- rename `used_storage` in the `backup-server:list` command

## 1.0.3 - 2020-10-22

- fix sorting on `youngest_backup_size `, `backup_size ` and `used_storage` in the `backup-server:list` command (#10)

## 1.0.2 - 2020-10-22

- allow more fields to be sorted in `backup-server:list` command (#9)

## 1.0.1 - 2020-10-22

- add possibility to sort sources in `backup-server:list` command (#7)

## 1.0.0 - 2020-10-21

- initial release
