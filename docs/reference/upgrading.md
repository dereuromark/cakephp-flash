# Upgrading

The CakePHP 5 branch uses current component/helper APIs. Follow [installation](../guide/installation.md) and review [composer.json](https://github.com/dereuromark/cakephp-flash/blob/master/composer.json) before updating an older application.

## Configuration and templates

The global `Flash` configuration now supplies defaults to both the component and helper. Existing explicit load options take precedence. Custom `order` and `ajaxDetectors` lists now replace their built-in lists instead of appending to them. Merge the restored `config/app.example.php` entry into your application configuration when you want shared defaults. `TransientFlash` holds messages at runtime and should not be copied into application configuration.

Keep `parent::initialize()` and the `void` return type when updating `AppController` and `AppView`. Provide all five application templates, including `info.php`.

## Message behavior

Use a positive stack limit. A zero or negative limit now fails when the component or helper loads. All component message paths enforce the configured stack limit. Transient stacks retain exactly that number of messages. `duplicate: false` suppresses identical controller entries in either store.

On AJAX requests, direct `set()` and `message()` now follow `noSessionOnAjax`, just as typed calls do. Component transient calls accept `Throwable` and normalize it to message text and code. Plugin-specific elements preserve their message type, and direct calls now honor the `plugin` option. AJAX headers also work for actions returning a response directly.

Helper transient calls now retain supplied options. A configured helper key is used by a parameterless `render()`, and type filtering also recognizes ordinary CakePHP core flash entries.

CakePHP 6 support is not declared. These changes keep the CakePHP 5 code ready for a separate compatibility upgrade once its requirements are known.
