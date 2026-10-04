# State
Updated: 2026-10-03 by Claude (Opus 5.5)

## Now

1.1.0 is released: tagged `v1.1.0` on `main`, and `main` and `develop` pushed. It loads through mai-package-loader from `mai-package.php`; `init.php` and `Mai_DOM_Bootstrap` are gone.

Consumers on 1.1: balloon-juice-plugin, the balloon-juice and horizonwesthappenings themes (committed locally, not pushed). The mai-slots engine and mai-content-areas use it through a local path repository.

## Next

Nothing open.

## Blocked / waiting on

Nothing.

## Verify

```sh
php -r 'require "vendor/autoload.php"; var_dump( class_exists( "Mai\\DOM\\Document" ) );'
```

After `composer install`, expect `bool(true)`. The engine's `php ~/LocalPackages/mai-slots/tests/run.php` exercises it most.

## Gotchas

- Bump `version` in `mai-package.php` with every release. The loader picks the newest copy by that number.
- Never delete or rename a released class. The loader treats a newer copy missing a file an older copy has as damaged, and drops it.
- No Composer `autoload` entry, on purpose. A consumer needs both this repo's and mai-package-loader's VCS entries in its own `composer.json`.
