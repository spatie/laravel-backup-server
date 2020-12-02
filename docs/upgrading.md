---
title: Upgrading
weight: 5
---

## From v1 to v2

- remove the `backup_hour` column in the `backup_server_sources` table.
- add a column `backup_server_sources` (varchar, 255) to the `backup_server_sources`. This column should hold a value cron expression that will determine when the source will be backed up. Here's the value when you want to run a backup each day at 2am:  `0 2 * * *`
