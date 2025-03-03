---
title: The clean up process
weight: 1
---

Over time the number of backups and the storage required to store them will grow. At some point you will want to clean up backups.

To clean up all backups this artisan command can be performed:

```bash
php artisan backup-server:cleanup
```

As mentioned in [the installation instructions](/docs/laravel-backup-server/v3/installation-setup), we recommend scheduling this command to run daily.

## What happens during cleanup

First, for each separate source, a clean up job to [clean up the source](docs/laravel-backup-server/v3/cleaning-up-backups/the-cleanup-process) will be dispatched. After that, for each separate destination, a clean up job to [clean up the destination](docs/laravel-backup-server/v1/cleaning-up-backups/the-cleanup-process) will be dispatched.

## Cleaning up a source

These steps will be performed when cleaning up a source

1. First, all `Backup` models that do not have a directory on the filesystem will be deleted.
2. Next old backups will be deleted. You can read more on we determine that a backup is "old" [in this section](/docs/laravel-backup-server/v3/cleaning-up-backups/determining-old-backups).
3. All backups that are mark as failed (their [backup process](/docs/laravel-backup-server/v3/taking-backups/the-backup-process) didn't complete fully) and are older than a day will be deleted.
4. Real backup size will be calculated. Because of the use of hard links in [the backup process](/docs/laravel-backup-server/v3/taking-backups/the-backup-process), the size of a backup will not match the size it actually takes on disk. Here, we are going to calculate what the real disk space usage is for each backup and save it in the `real_size_in_kb` on each `Backup`.

You can increase the default timeout for this calculation with the `backup_collection_size_calculation_timeout_in_seconds` value in the config file. This may be necessary for large backups, especially if you're backing up to a cloud volume.

## Cleaning up a destination

The package will delete any directory on the destination that does not belong to one of the backups on it.

## Removing a source

When you no longer need to support a source and want to remove all backups, the cleanup command will take care of it.
You can remove the source record in the `backup_server_sources` table and execute the cleanup job.
