---
title: Creating a source
weight: 2
---


The collection of files on a remote server that you wish to backup is represented by a `Source`.

A `Source` is an Eloquent model. It can be created like this.

```php
Destinations\BackupServer\Models\Source::create($attributes)
```

These attributes can be set

- `name` (required): the name of the host, you can name it anything you'd like
- `directory_name`: the name of the directory this source's backups are stored in on the destination disk. When empty, the source's id is used. See [naming the backup directory](#content-naming-the-backup-directory)
- `host` (required):  the hostname of the server you wish to backup
- `ssh_user`: the user name to use to ssh into the source server
- `ssh_port`: the port to use when connecting to the source server
- `ssh_private_key_file`: the path to a ssh key file to use when connecting to the source server
- `destination_id`: the id of the [destination](/docs/laravel-backup-server/v1/taking-backups/creating-a-destination) you wish to backup to

- `cron_expression` (required): the cron expression on which `backup-server:dispatch-backups` should create backups for this source

- `includes`: an array of paths you wish to backup. These can be relative to the home directory of the ssh user, or absolute
- `excludes`: an array of paths you wish to exclude from the backup. These should be relative to the paths giving in `includes`

- `pre_backup_commands`: an array of commands that should be executed on the source server prior to backing up. You could use this to dump the database to the filesystem.
- `post_backup_commands`: an array of commands that should be executed on the source server after the backup is complete. You could use this to clean databases that have been dumped to the filesystem.

The other attributes on the destination are used for [monitoring health](docs/laravel-backup-server/v1/monitoring-the-health-of-all-backups/the-monitoring-process) and [clean up](/docs/laravel-backup-server/v1/cleaning-up-backups/the-cleanup-process) of backups.

## Naming the backup directory

By default, the backups of a source are stored in a directory named after the source's id.

```
/1/backup-2020-01-01-000000/
```

Set `directory_name` to store them in a directory you can recognize instead. This makes browsing the destination disk a lot easier when you are backing up more than a handful of sources.

```php
Source::create([
    'name' => 'Acme production',
    'directory_name' => 'acme-production',
    // ...
]);
```

```
/acme-production/backup-2020-01-01-000000/
```

The value is used as a directory name as-is, so stick to characters that are safe on the destination's filesystem. It must be unique across sources, so that two sources can never write into the same directory.

Every backup stores the path it was written to, so changing `directory_name` never moves or breaks existing backups. They stay where they are and remain restorable; only backups made afterwards use the new directory.

### Existing installations

Sources created before this option existed have no `directory_name` and keep using their id, so there is nothing you need to do. If you do want to use it, add the column to the `backup_server_sources` table:

```php
Schema::table('backup_server_sources', function (Blueprint $table) {
    $table
        ->string('directory_name')
        ->after('name')
        ->nullable()
        ->unique();
});
```

