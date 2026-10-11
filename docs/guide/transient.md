# Transient messages

Transient messages belong to the current request. They are kept in runtime configuration rather than the session, so they do not survive a redirect.

In a controller:

```php
$this->Flash->transientSuccess('Preview generated.');
$this->Flash->transientWarning('This preview has not been saved.');
$this->Flash->transientMessage('Review pending.', ['type' => 'info']);
```

In a view, the same typed methods are available through the helper:

```php
$this->Flash->transientInfo('Showing a preview.');
```

Add these messages before the layout calls `render()`. The helper combines transient and session messages, orders them by type, and removes the rendered entries from their stores.

For a message at a specific point in a template, render it directly:

```php
<?= $this->Flash->message('This section is read only.', 'info') ?>
```

Direct rendering returns HTML immediately and does not add a message to either stack. The helper's transient methods accept their options, including `key`, `params` and `escape`; its configured limit applies per transient key.

The `TransientFlash` configuration namespace is runtime storage. Do not pre-populate it in `config/app.php`. Use the shared `Flash` key or individual load options for [configuration](../reference/configuration.md).
