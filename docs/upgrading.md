---
title: Upgrading
weight: 5
---

## From v3 to v4

#### Breaking changes

- **New column**: Add a nullable `paused_failed_notifications_until` column (type: `timestamp`) to the `backup_server_sources` table. This column allows you to specify when to resume failed notifications. 

**Migration**

Copy and paste this migration into your project:

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('backup_server_sources', function (Blueprint $table) {
            $table
                ->timestamp('paused_failed_notifications_until')
                ->after('healthy_maximum_storage_in_mb')
                ->nullable();
        });
    }
}

```

## From v2 to v3

v3 add compatability with Laravel 9 and Flysystem v3. We had to create this release to make sure our code works with the new dependencies. The public API was not changed.

You can upgrade without making any changes.

## From v1 to v2

- remove the `backup_hour` column in the `backup_server_sources` table.
- add a column `cron_expression` (varchar, 255) to the `backup_server_sources`. This column should hold a value cron expression that will determine when the source will be backed up. Here's the value when you want to run a backup each day at 2am:  `0 2 * * *`
