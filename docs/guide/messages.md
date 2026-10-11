# Messages and rendering

The component provides `success()`, `error()`, `warning()` and `info()`. Each selects the corresponding `templates/element/flash/` template.

```php
$this->Flash->warning('Please check your details.');
$this->Flash->info('The export is ready.');
```

`message()` accepts a type as its second argument:

```php
$this->Flash->message('The export is ready.', 'info');
$this->Flash->message('Please check your details.', ['type' => 'warning']);
```

Messages are escaped by the template from the [beginner guide](./getting-started.md). For trusted markup, pass `['escape' => false]`; the plugin forwards this as `params.escape` to the template.

## Stacks and ordering

Session messages are stored under `Flash.<key>`, with `flash` as the default key. Each key keeps the newest messages up to the component's configured limit. To use another stack:

```php
$this->Flash->success('Profile updated.', ['key' => 'profile']);
```

Render it in a view:

```php
<?= $this->Flash->render('profile') ?>
```

The helper displays errors, warnings, successes and information messages in that order. Other types follow them. Rendering consumes the messages it displays. Render only errors and leave the others for a later call:

```php
<?= $this->Flash->render('flash', ['types' => ['error']]) ?>
```

Use `['clear' => true]` to replace a session stack, or `['duplicate' => false]` to suppress an identical session or controller transient entry. Equality includes its message, type, key, element and parameters.

A custom type requires a matching template:

```php
$this->Flash->message('Review pending.', ['type' => 'pending']);
```

Create `templates/element/flash/pending.php`. For options shared by every call, see [Configuration](../reference/configuration.md).
