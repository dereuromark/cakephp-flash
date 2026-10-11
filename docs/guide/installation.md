# Installation

For this development branch, the application's root `composer.json` must allow development dependencies with `"minimum-stability": "dev"`. Set `"prefer-stable": true` to prefer stable packages when their constraints permit it.

From your application's root directory:

```sh
composer require dereuromark/cakephp-flash:dev-cake6
bin/cake plugin load Flash
```

Composer checks the supported PHP and CakePHP versions. This development branch targets CakePHP 6; see [composer.json](https://github.com/dereuromark/cakephp-flash/blob/cake6/composer.json) for the current constraints.

Continue with [Getting started](./getting-started.md) to load the component and helper and create the templates. The plugin does not need a database table or plugin routes.

