# fluid22/modules

Small helpers for structuring WordPress plugins as **modules**: a shared [League Container](https://github.com/thephpleague/container) instance, an abstract `Module` base class (hooks + templates), and an optional `Options` model around `get_option` / `update_option`.

**Package:** [`fluid22/modules` on Packagist](https://packagist.org/packages/fluid22/modules)  
**Source:** [github.com/fluid22/modules](https://github.com/fluid22/modules)

## Requirements

- PHP **^7.4 || ^8.0**
- WordPress (uses `apply_filters`, `get_option`, `update_option`, `delete_option`)
- Composer

## Installation

```bash
composer require fluid22/modules
```

Autoloading loads `Fluid22\Module\` from `src/` and **always** includes `src/helpers.php`, which defines the global `container()` / `start()` API in the `Fluid22\Module` namespace.

## Quick start

### 1. Bootstrap the container and modules

Early in your plugin (for example in the main plugin file after Composer’s autoloader):

```php
use function Fluid22\Module\start;

start( [
    \MyPlugin\Modules\Assets::class,
    \MyPlugin\Modules\Admin::class,
], 'my-plugin' );
```

`start()` registers each class with the container identified by the given key, resolves instances in order, and calls `setup()` on every instance that extends `Fluid22\Module\Module`.

**Multiple plugins on one site:** always pass a stable, plugin-specific key (your text domain works well) as the second argument to `start()` and `container()`. Each key gets its own isolated container, so bindings, the autowire setting, and resolved modules never collide across plugins. Omitting the key uses a shared `'default'` bucket — fine for a single consumer, unsafe if other plugins might also use this library.

After all modules have booted, the library fires:

```php
do_action( 'fluid22_modules_booted', string $key, array $modules );
```

Use this to sequence cross-plugin wiring.

### 2. Define a module

Place each module in its own directory and extend `Module`. Implement `setup()` to register actions, filters, shortcodes, and so on.

```php
<?php

namespace MyPlugin\Modules;

use Fluid22\Module\Module;

class Admin extends Module
{
    public function setup(): void
    {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
    }

    public function register_menu(): void
    {
        // ...
    }
}
```

The module’s **directory** is derived from the concrete class file path (`get_dir()`), so colocate `templates/` next to that class.

### 3. Templates

Put PHP templates under `{ModuleDirectory}/templates/`, named `{name}.html.php`. Subfolders are allowed using `/` in the template name (for example `emails/summary` → `templates/emails/summary.html.php`).

Allowed name characters: letters, digits, `_`, `-`, and `/` between segments. The loader resolves paths with `realpath()` so files cannot escape `templates/`.

```php
$html = $this->get_template( 'hero', [
    'title'   => __( 'Hello', 'my-plugin' ),
    'excerpt' => $text,
] );
```

`get_template()` returns **`false`** if the name is invalid or the file is missing.

Variables are passed as an array; **valid PHP variable names** are **`extract()`ed** into the template with **`EXTR_SKIP`**, inside a narrow scope so they cannot clobber internal state. **`$this`** in the template is the module instance. Invalid array keys (numeric keys, bad identifiers) are ignored.

### 4. Dependency injection

`container( string $key = null )` returns the `League\Container\Container` for that key, creating it on first access. By default, `setup_container()` delegates to `League\Container\ReflectionContainer` so constructors can type-hint dependencies when autowiring is enabled.

Disable autowiring (per-key via the filter’s second argument):

```php
add_filter( 'fluid22_container_autowire', function ( bool $enabled, string $key ): bool {
    return $key === 'my-plugin' ? false : $enabled;
}, 10, 2 );
```

Register bindings before `start()` against the same key:

```php
use function Fluid22\Module\container;

container( 'my-plugin' )->add( \MyPlugin\Contracts\Store::class, \MyPlugin\WpOptionsStore::class );
```

Containers are stored in **`$GLOBALS['fluid22_containers']`** keyed by the container key. For back-compat, the default bucket is also mirrored to **`$GLOBALS['fluid22_container']`**.

## Options model

`Fluid22\Module\Models\Options` is a thin, lazy wrapper over WordPress options with a required key prefix.

```php
use Fluid22\Module\Models\Options;

$opts = new Options( 'myplugin_' );

$opts->api_key = 'secret';
unset( $opts->old_flag ); // or $opts->remove( 'old_flag' );
$opts->save();
```

- Reads load from the database on first access via magic `__get`.
- Assignments mark keys dirty until `save()` calls `update_option`.
- `remove()` / `__unset()` queue `delete_option` on the next `save()`.
- Constructor: `new Options( ?string $prefix, ?bool $autoload )`. A **non-empty prefix is required** — pass it to the constructor, or define `protected string $prefix = '…';` in a subclass and pass `null`. Constructing without an effective prefix throws `InvalidArgumentException` so plugins can’t accidentally share the same option keys.
- The **`$autoload`** flag is passed as the third argument to `update_option()` (default `false`).

## Packagist updates

This repository includes a GitHub Actions workflow that calls Packagist’s **`update-package`** API when you push to `master`/`main` or push tags. Configure **`PACKAGIST_USER`** and **`PACKAGIST_TOKEN`** in the repo’s Actions secrets, or use Packagist’s built-in GitHub integration instead.

## License

MIT. See [`composer.json`](composer.json) for author metadata.
