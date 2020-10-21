---
title: Creating a destination
weight: 1
---


The location where backups will be stored is represented by a `Destination`. Mostly this will be a large block storage device that is connected to the server running backup server.

A `Destination` is an eloquent model. It can be created like this.

```php
Destinations\BackupServer\Models\Destination::create($attributes)
```

These two attributes are required:
- `name`: the name of this destination
- `disk_name`): the name of one of the disks configured in `config\filesystems.php`. The chosen disk must use the `local` driver

The other attributes on the destination are used for [monitoring health](docs/laravel-backup-server/v1/monitoring-the-health-of-all-backups/the-monitoring-process) and [clean up](/docs/laravel-backup-server/v1/cleaning-up-backups/the-cleanup-process) of backups.
