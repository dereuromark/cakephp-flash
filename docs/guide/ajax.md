# AJAX responses

For requests detected as AJAX, normal component calls such as `success()` and `set()` use the transient stack by default. Explicit transient calls work too. The component sends messages from its configured key in an `X-Flash` header before rendering the response.

The response body still belongs to your controller action. The plugin does not generate a JSON response body.

## Read the header with fetch

`fetch()` does not automatically set CakePHP's AJAX detection header. Set it explicitly:

```javascript
const response = await fetch('/articles/index', {
  headers: { 'X-Requested-With': 'XMLHttpRequest' },
});
const header = response.headers.get('X-Flash');
if (header) {
  const messages = JSON.parse(header);
  const container = document.getElementById('notifications');
  for (const message of messages) {
    const item = document.createElement('p');
    item.textContent = message.message;
    container.appendChild(item);
  }
}
```

Add the container to your page:

```html
<div id="notifications" role="status" aria-live="polite"></div>
```

Each entry contains `message`, `type` and `params`. Use `type` to select styling. Read the header before discarding the response; it may be absent when no messages were queued.

## Options and redirects

Set `headerKey` to an empty string to disable header output. Set `noSessionOnAjax` to `false` to keep normal component messages in the session; those session messages are not included in the header. Explicit transient messages still are.

Header injection on redirects requires `headerOnRedirect: true` and an AJAX request. A browser following a redirect with `fetch()` usually exposes only the final response's headers. For an AJAX operation, return its final response directly when the client needs the message header.

For a custom AJAX detector or header name, see [Configuration](../reference/configuration.md).
