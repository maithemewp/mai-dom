# State
Updated: 2026-10-04 by Claude (Opus 5.5)

## Now

1.1.1 is committed on `develop`, not pushed or tagged. It fixes `Element::children()`, which returned nothing on PHP 8.4 because it read a collection PHP only added in 8.5, and adds `tests/run.php`. 1.1.0 is released on `main`.

## Next

1. Ask Mike: push `develop`, fast-forward `main`, tag `v1.1.1`.
2. The mai-slots engine works around the bug in `Dom\Fragment::children()`; once it requires `^1.1.1` it can call `children()` again.

## Blocked / waiting on

Mike's yes for the push and tag.

## Verify

```sh
php tests/run.php
```

Expect `41 passed, 0 failed`, on PHP 8.4 and on the newest PHP. Herd's are in `~/Library/Application Support/Herd/bin/php84` and `php85`.

## Gotchas

- Bump `version` in `mai-package.php` with every release. The loader picks the newest copy by that number.
- Never delete or rename a released class. The loader treats a newer copy missing a file an older copy has as damaged, and drops it.
- No Composer `autoload` entry, on purpose. A consumer needs both this repo's and mai-package-loader's VCS entries in its own `composer.json`.
