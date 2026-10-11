# Configuration

Pass options when loading the component and helper. The plugin does not read a global `Flash` array from `config/app.php`.

```php
$this->loadComponent('Flash.Flash', ['limit' => 5]);
```

In `AppView::initialize()`:

```php
$this->loadHelper('Flash.Flash', ['limit' => 5]);
```

Configure the component and helper separately. They share the transient stack, so a helper append also trims controller transient entries to the helper limit. Use the same `key` and `limit` on both when changing their defaults.

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
