# Configuration

Set shared defaults under `Flash` in your application's `config/app.php` or `config/app_local.php`:

```php
'Flash' => [
    'key' => 'notifications',
    'limit' => 5,
    'params' => ['class' => 'notice'],
],
```

Merge this entry into the existing configuration array. Both the component and helper read it when they load, so one `key` and `limit` apply to both. See the complete [example configuration](https://github.com/dereuromark/cakephp-flash/blob/master/config/app.example.php).

For runtime setup before loading either class, the equivalent is:

```php
Configure::write('Flash', [
    'key' => 'notifications',
    'limit' => 5,
]);
```

Import `Cake\Core\Configure` when using that form.

Explicit component or helper load options override shared values:

```php
$this->loadComponent('Flash.Flash', ['limit' => 3]);
```

The helper still uses the shared limit of five unless you override it too. They share the transient stack, so a helper append trims existing controller entries to the helper's limit. Use matching settings unless you need different behavior.

Precedence is load options, shared `Flash` settings, then built-in defaults. An explicit array option such as `params` replaces the shared value for that option. Configure defaults before loading the classes; changing `Configure` afterwards does not update existing instances.

## Component

| Option | Default | Behavior |
| --- | --- | --- |
| `key` | `flash` | Session/transient stack key; header output reads this key. |
| `limit` | `10` | Newest entries retained per key, separately in session and transient storage; use a positive integer. |
| `params` | `[]` | Default parameters supplied to elements. |
| `clear` | `false` | Replace existing session entries when true. |
| `duplicate` | `true` | Allow identical entries; false suppresses identical controller entries. |
| `headerKey` | `X-Flash` | AJAX response header; empty string disables it. |
| `headerOnRedirect` | `false` | Also inject the header before AJAX redirects. |
| `noSessionOnAjax` | `true` | Route normal AJAX message calls into transient storage. |
| `ajaxDetectors` | `['ajax']` | Request detectors used for AJAX handling and header output. |

Typed calls select their corresponding element; `message()` defaults to `info`. Direct `set()`, `message()` and `transientMessage()` calls accept `type`, `element`, `plugin`, `key`, `params` and `escape`. Typed methods choose their type and element; they also accept `plugin`, `key`, `params` and `escape`. Session calls also accept `clear` and `duplicate`. Controller transient calls support `duplicate`.

A `Throwable` passed to the component becomes its message text, with its code stored in `params.code` unless you supplied that parameter yourself.

## Helper

| Option | Default | Behavior |
| --- | --- | --- |
| `key` | `flash` | Stack used when `render()` has no explicit key. |
| `limit` | `10` | Newest transient entries retained per key when the helper appends; use a positive integer. |
| `order` | `['error', 'warning', 'success', 'info']` | Type order when rendering; other types follow. |
| `params` | `[]` | Default element parameters. |

The helper's limit applies when it adds transient messages. It does not trim session messages supplied by the component. Render options can select `types`; direct `message()` also accepts element options as its third argument.
