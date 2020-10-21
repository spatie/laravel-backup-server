---
title: The monitoring process
weight: 1
---

The package can check the health of backups for every source, and the destination is healthy.

This check happens in the `backup-server:monitor` command that should be scheduled as shown in [the installation instructions](/docs/laravel-backup-server/v1/installation-setup).

This check will [fire of events](/docs/laravel-backup-server/v1/monitoring-the-health-of-all-backups/events) and [sends out notifications](/docs/laravel-backup-server/v1/sending-notifications/sending-notifications) for each (un)healthy source and destination.
