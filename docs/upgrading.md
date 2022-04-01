---
title: Upgrading
weight: 5
---

## From v2 to v3

v3 add compatability with Laravel 9 and Flysystem v3. We had to create this release to make sure our code works with the new dependencies. The public API was not changed.

You can upgrade without making any changes.

### From v1 to v2

- remove the `backup_hour` column in the `backup_server_sources` table.
- add a column `cron_expression` (varchar, 255) to the `backup_server_sources`. This column should hold a value cron expression that will determine when the source will be backed up. Here's the value when you want to run a backup each day at 2am:  `0 2 * * *`
