# CakePHP Flash Plugin

[![CI](https://github.com/dereuromark/cakephp-flash/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/dereuromark/cakephp-flash/actions/workflows/ci.yml?query=branch%3Amaster)
[![Coverage Status](https://img.shields.io/codecov/c/github/dereuromark/cakephp-flash/master.svg)](https://codecov.io/github/dereuromark/cakephp-flash/branch/master)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-flash/v/stable.svg)](https://packagist.org/packages/dereuromark/cakephp-flash)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.1-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/dereuromark/cakephp-flash/license.svg)](LICENSE)
[![Total Downloads](https://poser.pugx.org/dereuromark/cakephp-flash/d/total.svg)](https://packagist.org/packages/dereuromark/cakephp-flash)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-yellow.svg)](https://github.com/php-fig-rectified/fig-rectified-standards)

A plugin for more powerful flash messages in your CakePHP apps.

This branch is for **CakePHP 5.1+**. See [version map](https://github.com/dereuromark/cakephp-flash/wiki#cakephp-version-map) for details.

## Features

- AJAX header support
- Limit of messages per stack key
- Transient flash message support (non persistent, current request only)
- By default 4 types (one more): error, warning, success, info
- Ordered output (error, warning, success, info) and output filtering per type

## Demo
See [sandbox demos](https://sandbox.dereuromark.de/sandbox/flash-examples).

## Documentation
See [/docs](docs/README.md).
