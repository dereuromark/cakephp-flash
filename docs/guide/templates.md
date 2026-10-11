# Templates

The plugin uses application elements rather than shipping a CSS framework. Provide `default.php`, `success.php`, `error.php`, `warning.php` and `info.php` under `templates/element/flash/`.

Use the [beginner template](./getting-started.md) in each file, or move the shared markup into `templates/element/flash/message.php` and use this in each typed element:

```php
<?= $this->element('flash/message', ['message' => $message, 'params' => $params]) ?>
```

The element receives the message and its `params` array. Check `params.escape` before outputting raw markup. Your templates decide the styles and accessibility markup.

Select an element from another loaded plugin with the component's `plugin` option:

```php
$this->Flash->success('Saved.', ['plugin' => 'MyUi']);
```

This uses `MyUi`'s `templates/element/flash/success.php`. The message type remains `success`, so ordering and filtering still work.
