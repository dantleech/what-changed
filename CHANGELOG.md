# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.4.1] - 2020-11-22

- Use `author['login']` in preference to `comitter['login]` (as github
  started using `web-flow` as the comitter user)

## [0.3.0] - 2020-06-20

- Support using the composer github oauth key.
- Support for composer 2.

## [0.2.0] - 2018-12-15

- Removed archive functionality, now uses a single lock file.
- Improved report to show author, hash and added option to show the full
  commit message.
- Handle runtime errors that may happen during composer update (e.g. exceeding
  API limit).
- Various refactoring.
