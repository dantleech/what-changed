What Changed?
=============

[![Build Status](https://travis-ci.org/dantleech/what-changed.svg?branch=master)](https://travis-ci.org/dantleech/what-changed)

![What Changed?](https://user-images.githubusercontent.com/530801/49700600-d3d10a00-fbd8-11e8-8235-cf3dbb026f32.png)

Generates change reports when you update [Composer](https://getcomposer.org).

This plugin makes a copy of your lock files before a composer update, and then
compares the new file with the old one. It then calls the Github API to get
the _commit messages_ for the differences between any upgraded packages.

**Features**:

- Shows the commit messages for upgraded packages.

**Current limitations / features**:

- Only packages hosted on Github are supported.
- Authentication not supported (can exceed the github API limit).

```bash
$ composer update

#... composer upates ...#
dantleech/what-changed: 1 removed
  - acme/removed

dantleech/what-changed: 8 new
  - infection/infection
  - padraic/humbug_get_contents
  - padraic/phar-updater
  - pimple/pimple
  - psr/container
  - symfony/yaml
  - theseer/tokenizer
  - webmozart/assert

dantleech/what-changed: 2 updated

  composer/composer b89daf53..d8aef3af

    [2018-10-14 14:19:08] a1ab75a7 dmanners composer/composer#7159: make the remove command to a regex lookup on package name  - if you have multiple...
    [2018-11-26 19:09:26] 66d84f60 Seldaek Fix pattern matching for remove wildcard, refs #7715
    [2018-11-27 11:22:32] 17fd933f Seldaek Update dependencies
    [2018-11-27 13:26:03] 489e0d4b Seldaek Add support for imagemagick <3.3, refs #7762
    [2018-11-27 15:27:01] 5ce55600 meyerbaptiste Fix support for imagemagick <3.3, refs #7762
    [2018-11-28 07:44:45] 7ab633a2 Seldaek Prepare 1.8.0 changelog
    [2018-11-29 14:25:01] ab165cfc johnstevenson Update xdebug-handler, fixes #7807
    [2018-12-03 09:21:52] 02ee50ac Seldaek Prepare 1.8.0
    [2018-12-03 09:31:16] d8aef3af Seldaek Release 1.8.0

  phpstan/phpstan c896a1a3..f0252a5a

    [2018-12-03 20:43:08] 7888e6ed ondrejmirtes Support for is_countable()
    [2018-12-03 21:26:56] d98c7ca1 ondrejmirtes Support for JSON_THROW_ON_ERROR
    [2018-12-03 23:21:12] f0252a5a ondrejmirtes Support for array_key_first/array_key_last
```

Installation
------------

```
$ composer require dantleech/what-changed --dev
```

Usage
-----

The report is automatically generated and dumped to the console _each time_ you update your dependencies.

You can also (re) generate the report at any time by calling the `composer what-changed` command:

```
$ composer what-changed
```

This as the plugin makes a copy of your old composer lock file, you can review
the last reported changes at any time with the `what-changed` command:

```
$ composer what-changed
```

Options
-------

- `--full-message`: Show the full commit message
- `--merge-commits`: Include merge commits

