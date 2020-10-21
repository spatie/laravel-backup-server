---
title: Introduction
weight: 1
---

This package backs up one or more servers that use either the `ext3` or `ext4` filesystem, which is the default file system for many *nix distributions. When a backup contains files also present in a previous backup, deduplication using hard links will be performed. Even though you will see full backups in the filesystem, only changed files will take up disk space.

The package can also search for file names and content in backups, clean up old backups, and notify you when there were problems running the backups.
