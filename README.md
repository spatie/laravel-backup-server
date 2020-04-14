# Back up multiple applications

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-backup-server.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-backup-server)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/spatie/laravel-backup-server/run-tests?label=tests)](https://github.com/spatie/laravel-backup-server/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-backup-server.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-backup-server)

This package backs up one or more servers that use either the `ext3` or `ext4` filesystem, which is the default file system for many *nix distributions. When a backup contains files also present in a previous backup, deduplication using hard links will be performed. Even though you will see full backups in the filesystem, only changed files will take up disk space.

The package can also search for file names and content in backups, clean up old backups, and notify you when there were problems running the backups.

## Support us

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/spatie). 
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## Documentation

Extensive documention is available [on our documentation site](https://docs.spatie.be/laravel-backup-server/)

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
