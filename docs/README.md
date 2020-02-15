# Flash Plugin Documentation

## Install
Run
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
In your AppView:
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
For transient messages:
```php
$this->Flash->transientSuccess('I am not persisted in session');
$this->Flash->transientError('I am not persisted in session');
$this->Flash->transientInfo('I am not persisted in session');
$this->Flash->transientWarning('I am not persisted in session');
```

In your view you can also add transient flash messages:

```php
$this->Flash->addTransientSuccess('I am not persisted in session');
$this->Flash->addTransientError('I am not persisted in session');
$this->Flash->addTransientMessage('Oh oh', ['type' => 'custom']);

```
Note: Do not try to add anything in the layout below the `render()` call as that would not be included anymore.

If you want to just output a message anywhere in your template (like a warning block):
```php
echo $this->message('Hey, I am an info block');
```

### Rendering each type in a separate process
The following would only render (and remove) the error messages:
```php
<?= $this->Flash->render('flash', ['types' => ['error']]) ?>
```

### Use in AJAX requests

AJAX calls will respond with a JSON encoded array of messages (each item containing a message key, a type key and params key) within the `X-Flash` header.

Example of how to handle AJAX flash messages:
```
    if(jqXHR.getResponseHeader('X-Flash') && typeof JSON.parse(jqXHR.getResponseHeader('X-Flash')) == 'object' && typeof JSON.parse(jqXHR.getResponseHeader('X-Flash')) != 'undefined') {
        const flash = JSON.parse(jqXHR.getResponseHeader('X-Flash'))[0];
        customFunctionToNotifyFlashMessages(flash.message, flash.type);
    }
```

## Customization

### Component Options

Option |Description
:----- | :----------
limit | Max message limit per key (first in, first out), defaults to `10`.

as well as the CakePHP core component options.

### Helper Options

Option |Description
:----- | :----------
limit | Max message limit per key (first in, first out), defaults to `10`.
order | Order of output, types default to `['error', 'warning', 'success', 'info']`, all others are rendered last.

### Flash layouts
You should have `default.ctp`, `error.ctp`, `warning.ctp`, `success.ctp`, and `info.ctp` templates.

The `src/Template/Element/flash/error.ctp` could look like this:
```html
<?php
if (!isset($params['escape']) || $params['escape'] !== false) {
    $message = h($message);
}
?>
<div class="alert alert-danger"><?= $message ?></div>
```
You can copy and adjust the existing ones from the `tests/TestApp/Template/Element/flash/` folder (bootstrap) or from the cakephp/app repo (foundation).
