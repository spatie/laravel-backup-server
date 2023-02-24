# Changelog

All notable changes to `laravel-backup-server` will be documented in this file

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
