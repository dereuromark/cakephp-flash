# CakePHP Flash Plugin

[![CI](https://github.com/dereuromark/cakephp-flash/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/dereuromark/cakephp-flash/actions/workflows/ci.yml?query=branch%3Amaster)
[![Coverage Status](https://img.shields.io/codecov/c/github/dereuromark/cakephp-flash/master.svg)](https://codecov.io/github/dereuromark/cakephp-flash/branch/master)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat)](https://phpstan.org/)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-flash/v/stable.svg)](https://packagist.org/packages/dereuromark/cakephp-flash)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.2-8892BF.svg)](https://php.net/)
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
Read the [documentation website](https://dereuromark.github.io/cakephp-flash/), starting with [installation](https://dereuromark.github.io/cakephp-flash/guide/installation) and [Getting started](https://dereuromark.github.io/cakephp-flash/guide/getting-started).
