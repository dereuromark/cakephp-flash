# CakePHP Flash Plugin

[![Build Status](https://api.travis-ci.org/dereuromark/cakephp-flash.png?branch=master)](https://travis-ci.org/dereuromark/cakephp-flash)
[![Coverage Status](https://img.shields.io/codecov/c/github/dereuromark/cakephp-flash/master.svg)](https://codecov.io/github/dereuromark/cakephp-flash?branch=master)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-flash/v/stable.svg)](https://packagist.org/packages/dereuromark/cakephp-flash)
[![Minimum PHP Version](http://img.shields.io/badge/php-%3E%3D%205.5-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/dereuromark/cakephp-flash/license.png)](https://packagist.org/packages/dereuromark/cakephp-flash)
[![Total Downloads](https://poser.pugx.org/dereuromark/cakephp-flash/d/total.png)](https://packagist.org/packages/dereuromark/cakephp-flash)

A plugin for more powerful flash messages in your CakePHP apps.

**This branch is for CakePHP 3.x**

## Features

- AJAX header support
- Limit of messages per stack key
- Transient flash message support (non persistent, current request only)
- By default 4 types (one more): error, warning, success, info
- Ordered output (error, warning, success, info) and output filtering per type


## Install

### Composer (preferred)
```
composer require dereuromark/cakephp-flash
```

## Setup
Enable the plugin in your `config/bootstrap.php` or call
```
bin/cake plugin load Flash
```

You can simply modify the existing config entries in your `config/app.php`:
 ```php
	'Flash' => [
		...
	],
```

### Include the component
In your AppController:
```php
public function initialize() {
	parent::initialize();

	$this->loadComponent('Flash.Flash');
}
```

### Include the helper
```php
public function initialize() {
	$this->loadHelper('Flash.Flash');
}
```

Your layout does not need any modification, the included helper call is the same as with the core one:
```html
<?= $this->Flash->render() ?>
```

## Usage

Anywhere in your controller layer you can now use
```php
$this->Flash->success('Yeah');
// or
$this->Flash->error('Oh <b>NO</b>', ['escape' => false]);
```

In your view you can also add transient flash messages:

```php
$this->addTransientMessage('I am not persisted in session');
$this->addTransientMessage('Oh oh', ['element' => 'error']);
```
Note: Do not try to add anything in the layout below the `render()` call as that would not be included anymore.

If you want to just output a message anywhere in your template (like a warning block):
```php
echo $this->message('Hey, I am an info block');
```

## Customization

### Flash layouts
The `src/Template/Element/Flash/error.ctp` could look like this:
```html
<?php
if (!isset($params['escape']) || $params['escape'] !== false) {
	$message = h($message);
}
?>
<div class="alert alert-danger"><?= $message ?></div>
```
