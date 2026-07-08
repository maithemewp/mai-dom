# Changelog

All notable changes to `mai-dom` are documented here.

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) · Versioning: [Semantic Versioning](https://semver.org/).

## [1.0.1] — 2026-07-08

### Changed

- Added a `.gitattributes` with `export-ignore` so dev-only paths are stripped from the Composer dist archive, keeping a bundled copy of this package out of consumers' production trees. No runtime change.

## [1.0.0] — 2026-06-12

First stable release: the HTML-insertion API is finalized (explicit `*Html`
naming) and PHP 8.4-correct. Below changes are relative to the 0.1.0 preview.

### Fixed

- **PHP 8.4 compatibility for HTML insertion.** `appendHtml`/`prependHtml`/`beforeHtml`/`afterHtml`/`replaceWithHtml` no longer call `Dom\Element::insertAdjacentHTML()`, which is PHP 8.5+ only ([php-src #16614](https://github.com/php/php-src/pull/16614)) and broke the package's `>=8.4` floor. They now parse via the `innerHTML` setter (the same mechanism `wrapWith()` uses) and splice with the native WHATWG node methods (`append`/`prepend`/`before`/`after`/`replaceWith`), which exist on 8.4 and 8.5 alike.

- `outerHtml()` now calls `Dom\HTMLDocument::saveHtml()` with the canonical lowercase-`html` casing (was `saveHTML`, the legacy `DOMDocument` spelling). Behavior is identical at runtime (PHP method names are case-insensitive); this matches the stub so static analysis stops flagging it.

### Changed

- Renamed HTML-insertion methods so every HTML-parsing insert carries the `Html` suffix (consistency): `append()` → `appendHtml()`, `prepend()` → `prependHtml()`, `insertBeforeHtml()` → `beforeHtml()`, `insertAfterHtml()` → `afterHtml()`. `replaceWithHtml()` and `replaceWithText()` are unchanged.

### Considered (not implemented)

- A literal-text insertion family (`appendText`/`prependText`/`beforeText`/`afterText`) mirroring the `*Html` methods. Deferred as YAGNI: `text()` already sets an element's text content and `replaceWithText()` covers element replacement; each would be a trivial native `append($text)` call to add if a concrete need appears.

## [0.1.0] — 2026-05-15

### Added

- Initial release.
- `Mai\DOM\Document` — wrapper around PHP 8.4's `Dom\HTMLDocument`. Methods: `from()`, `unwrap()`, `query()`, `queryAll()`, `toHtml()`, `minify()`, `walkNodes()`.
- `Mai\DOM\Element` — chainable single-element wrapper. Methods: `unwrap()`, `query()`, `queryAll()`, `tag()`, `attr()`, `removeAttr()`, `hasClass()`, `addClass()`, `removeClass()`, `toggleClass()`, `html()`, `text()`, `outerHtml()`, `append()`, `prepend()`, `insertBeforeHtml()`, `insertAfterHtml()`, `replaceWithHtml()`, `replaceWithText()`, `wrapWith()`, `remove()`, `parent()`, `children()`. Implements `__toString()` (returns outer HTML).
- `Mai\DOM\NodeList` — iterable collection wrapper for query results. Methods: `each()`, `map()`, `filter()`, `first()`, `last()`, `toArray()`. Implements `IteratorAggregate` and `Countable`.
- `Mai_DOM_Bootstrap` — shared autoloader registry that picks the highest registered version across plugins on the same WordPress install (same pattern as [`maithemewp/mai-logger`](https://github.com/maithemewp/mai-logger)).
