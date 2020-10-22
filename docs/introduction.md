---
title: Introduction
weight: 1
---

This package backs up one or more servers that use either the `ext3` or `ext4` filesystem, which is the default file system for many *nix distributions. When a backup contains files also present in a previous backup, deduplication using hard links will be performed. Even though you will see full backups in the filesystem, only changed files will take up disk space.

The package can also search for file names and content in backups, clean up old backups, and notify you when there were problems running the backups.

In this video you'll see a quick demonstration of the package.

<div style="padding:56.25% 0 0 0;position:relative;"><iframe src="https://player.vimeo.com/video/470787843?title=0&byline=0&portrait=0" style="position:absolute;top:0;left:0;width:100%;height:100%;" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe></div><script src="https://player.vimeo.com/api/player.js"></script>
