# Changelog

All notable changes to `mai-dom` are documented here.

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) · Versioning: [Semantic Versioning](https://semver.org/).

## [0.1.0] — 2026-05-15

### Added

- Initial release.
- `Mai\DOM\Document` — wrapper around PHP 8.4's `Dom\HTMLDocument`. Methods: `from()`, `unwrap()`, `query()`, `queryAll()`, `toHtml()`, `minify()`, `walkNodes()`.
- `Mai\DOM\Element` — jQuery-flavored single-element wrapper. Methods: `unwrap()`, `query()`, `queryAll()`, `tag()`, `attr()`, `removeAttr()`, `hasClass()`, `addClass()`, `removeClass()`, `toggleClass()`, `html()`, `text()`, `outerHtml()`, `append()`, `prepend()`, `insertBeforeHtml()`, `insertAfterHtml()`, `replaceWithHtml()`, `replaceWithText()`, `wrapWith()`, `remove()`, `parent()`, `children()`. Implements `__toString()` (returns outer HTML).
- `Mai\DOM\NodeList` — iterable collection wrapper for query results. Methods: `each()`, `map()`, `filter()`, `first()`, `last()`, `toArray()`. Implements `IteratorAggregate` and `Countable`.
- `Mai_DOM_Bootstrap` — shared autoloader registry that picks the highest registered version across plugins on the same WordPress install (same pattern as [`maithemewp/mai-logger`](https://github.com/maithemewp/mai-logger)).
