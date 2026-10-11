# CakePHP Flash Plugin

[![CI](https://github.com/dereuromark/cakephp-flash/actions/workflows/ci.yml/badge.svg?branch=cake6)](https://github.com/dereuromark/cakephp-flash/actions/workflows/ci.yml?query=branch%3Acake6)
[![Coverage Status](https://img.shields.io/codecov/c/github/dereuromark/cakephp-flash/cake6.svg)](https://codecov.io/github/dereuromark/cakephp-flash/branch/cake6)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat)](https://phpstan.org/)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-flash/v/stable.svg)](https://packagist.org/packages/dereuromark/cakephp-flash)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.5-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/dereuromark/cakephp-flash/license.svg)](LICENSE)
[![Total Downloads](https://poser.pugx.org/dereuromark/cakephp-flash/d/total.svg)](https://packagist.org/packages/dereuromark/cakephp-flash)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-purple.svg?style=flat-square)](https://github.com/php-fig-rectified/fig-rectified-standards)

A plugin for more powerful flash messages in your CakePHP apps.

See [composer.json](composer.json) for the supported versions and the [version map](https://github.com/dereuromark/cakephp-flash/wiki#cakephp-version-map) for older branches.

## Features

- AJAX header support
- Limit of messages per stack key
- Transient flash message support (non persistent, current request only)
- By default 4 types (one more): error, warning, success, info
- Ordered output (error, warning, success, info) and output filtering per type

## Demo
See [sandbox demos](https://sandbox.dereuromark.de/sandbox/flash-examples).

## Documentation
For this development branch, start with [installation](docs/guide/installation.md) and [getting started](docs/guide/getting-started.md). The [documentation website](https://dereuromark.github.io/cakephp-flash/) covers the released CakePHP 5 branch.
