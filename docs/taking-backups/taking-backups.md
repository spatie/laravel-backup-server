---
title: Taking backups
weight: 3
---

After you've defined a source and a destination, backups can be created. To learn more about what happens during a backup, read [this page on the backup process](/docs/laravel-backup-server/v1/taking-backups/the-backup-process).

## Automatically creating backups

The most common way to do this is by hourly scheduling the `backup-server:dispatch-backups` command [like shown in the installation instructions](/docs/laravel-backup-server/v1/installation-setup). The backup process for a source will start when the current hour matches the value in the `backup_hour` for that source.

## Manually creating a backup

You can manually create a backup by executing this command.

```bash
php artisan backup-server:backup <name-of-source>
```


