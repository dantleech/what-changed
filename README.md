What Changed?
=============

[![Build Status](https://travis-ci.org/dantleech/what-changed.svg?branch=master)](https://travis-ci.org/dantleech/what-changed)

Generates change reports when you update composer.

This plugin _archives_ your lock files, when you update it compares the new file with the previous one and then
calls the Github API to get the _commit messages_ for the differences between any upgraded packages.

**Features**:

- Shows the commit messages for upgraded packages.

**Current limitations / features**:

- Only packages hosted on Github are supported.
- Lock files are archived until they are manually delelted (in `vendor/composer/archive`).

```bash
$ composer what-changed
dantleech/what-changed: 19 changed

  [UPD] composer/composer 6ed7215fed..4301c19ce3

    [2018-12-03 09:21:52] Prepare 1.8.0
    [2018-12-03 09:39:36] Update target for master

  [UPD] jetbrains/phpstorm-stubs 693fe4c896..88e48ed150

    [2018-12-06 12:04:32] Fix typo in MongoDB\Driver\WriteConcern::getJournal
    [2018-12-07 14:44:26] Merge pull request #463 from alcaeus/fix-mongodb-writeconcern-typo  Fix typo in MongoDB\Driver\WriteConcern::getJournal

  [UPD] monolog/monolog a335f6f1a5..fd8c787753

  [UPD] phpactor/completion f5963d8b18..89fa8c6258

    [2018-12-03 22:29:55] do not show suggestions for instance members on static calls and vice-versa
    [2018-12-03 22:37:34] fixes test expectation

  [UPD] phpactor/worse-reflection f1efd01a7a..531509a405

    [2018-12-08 12:28:28] Require Support (#52)  * support for require / include    * renamed require to include walker    * handle binary expressio...
```

Installation
------------

```
$ composer require dantleech/what-changed
```

Usage
-----

The report is automatically generated and dumped to the console _each time_ you update your dependencies.

You can also (re) generate the report at any time by calling the `composer what-changed` command:

```
$ composer what-changed
```

This plugin archives your lock files, so you can see for an arbitrary number of lock files with the `--limit` option:

```
$ composer what-changed --limit=10
```

This will show the changes for the past 10 archived lock files.
