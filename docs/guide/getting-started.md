# Getting started

After [installation](./installation.md), use the plugin component in `src/Controller/AppController.php`. Replace an existing `$this->loadComponent('Flash');` line with the plugin line below; keep your other initialization code.

```php
public function initialize(): void {
    parent::initialize();

    $this->loadComponent('Flash.Flash');
}
```

Use the matching helper in `src/View/AppView.php`. Replace an existing explicit `loadHelper('Flash')` call rather than loading the same alias twice:

```php
public function initialize(): void {
    parent::initialize();

    $this->loadHelper('Flash.Flash');
}
```

## Create the message templates

Create `templates/element/flash/info.php`:

```php
<?php
if (($params['escape'] ?? true) !== false) {
    $message = h($message);
}
?>
<div class="flash-message" role="status"><?= $message ?></div>
```

Copy that file to `default.php`, `success.php`, `error.php` and `warning.php` in the same directory. These templates work without a CSS framework. Add your own styles later; see [Templates](./templates.md) for a shared template.

In `templates/layout/default.php`, place this before the page content. A CakePHP application usually already has this call:

```php
<?= $this->Flash->render() ?>
<?= $this->fetch('content') ?>
```

## Display a message

Add this to a controller action that renders a page:

```php
$this->Flash->success('Your changes were saved.');
```

Load that page. The layout displays the message once and removes it from the session. To keep a message until the next page after a redirect:

```php
$this->Flash->success('Your changes were saved.');

return $this->redirect(['action' => 'index']);
```

For a message that only belongs to the current response, use [transient messages](./transient.md). For `fetch()` requests, follow [AJAX responses](./ajax.md).
