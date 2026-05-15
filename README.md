# Mai DOM

Ergonomic, chainable wrapper around PHP 8.4's [`Dom\HTMLDocument`](https://wiki.php.net/rfc/domdocument_html5_parser). Closes the gap between PHP's still-clunky DOM ergonomics and the jQuery-style developer experience.

Versioned and drop-in safe — multiple plugins on the same WordPress install can each bundle their own copy of `mai-dom`; the highest registered version wins at runtime via a shared bootstrap registry (same pattern as [maithemewp/mai-logger](https://github.com/maithemewp/mai-logger)).

---

## Requirements

- **PHP 8.4+** — `Dom\HTMLDocument` is a PHP 8.4 feature.
- **WordPress** — uses `ABSPATH` as a load guard; bootstrap autoload runs from Composer's `vendor/autoload.php`.

---

## Installation

Add to a plugin or theme's `composer.json`:

```json
{
    "require": {
        "maithemewp/mai-dom": "^0.1"
    }
}
```

Then `composer install`. The bootstrap runs automatically when `vendor/autoload.php` is required.

### Local development

```json
{
    "repositories": [
        { "type": "path", "url": "~/LocalPackages/mai-dom" }
    ],
    "require": {
        "maithemewp/mai-dom": "*"
    }
}
```

---

## Quick start

```php
use Mai\DOM\Document;

$dom = Document::from( '<p>Hello <strong>world</strong></p>' );

// Find and mutate.
$dom->query( 'strong' )?->addClass( 'highlight' );

// Render back to HTML.
echo $dom->toHtml();
// → <p>Hello <strong class="highlight">world</strong></p>
```

---

## API

### `Mai\DOM\Document`

| Method | Returns | Notes |
|--------|---------|-------|
| `static from(string $html)` | `Document` | Parse an HTML fragment. |
| `unwrap()` | `Dom\HTMLDocument` | Escape hatch — the underlying document. |
| `query(string $selector)` | `Element\|null` | First match. |
| `queryAll(string $selector)` | `NodeList` | All matches. |
| `toHtml()` | `string` | Body innerHTML, trimmed. |
| `minify()` | `Document` | Collapse repeated whitespace in text nodes. |
| `static walkNodes(Node, callable)` | `void` | Recursive walker (advanced). |

### `Mai\DOM\Element`

| Method | Returns | Notes |
|--------|---------|-------|
| `unwrap()` | `Dom\HTMLElement` | Escape hatch — the underlying node. |
| `query(string $selector)` | `Element\|null` | Scoped to descendants. |
| `queryAll(string $selector)` | `NodeList` | Scoped to descendants. |
| `tag()` | `string` | Lowercase tag name. |
| `attr(string $name)` | `string` | Get attribute (empty string if absent). |
| `attr(string $name, string $value)` | `Element` | Set attribute, chainable. |
| `removeAttr(string $name)` | `Element` | |
| `hasClass(string $class)` | `bool` | |
| `addClass(string ...$classes)` | `Element` | |
| `removeClass(string ...$classes)` | `Element` | |
| `toggleClass(string $class)` | `Element` | |
| `html()` | `string` | Get innerHTML. |
| `html(string $html)` | `Element` | Set innerHTML. |
| `text()` | `string` | Get textContent. |
| `text(string $text)` | `Element` | Set textContent. |
| `outerHtml()` | `string` | Serialize this element including itself. |
| `append(string $html)` | `Element` | Insert at end of children. |
| `prepend(string $html)` | `Element` | Insert at start of children. |
| `insertBeforeHtml(string $html)` | `Element` | Insert as previous sibling. |
| `insertAfterHtml(string $html)` | `Element` | Insert as next sibling. |
| `replaceWithHtml(string $html)` | `void` | Replace with parsed HTML, detach this node. |
| `replaceWithText(string $text)` | `void` | Replace with literal text. |
| `wrapWith(string $html)` | `Element` | Wrap this element inside new HTML container. |
| `remove()` | `void` | Detach from DOM. |
| `parent()` | `Element\|null` | |
| `children()` | `NodeList` | Element children only (no text nodes). |
| `__toString()` | `string` | Same as `outerHtml()`. |

### `Mai\DOM\NodeList`

Implements `IteratorAggregate`, `Countable`. Iterable with `foreach`.

| Method | Returns |
|--------|---------|
| `each(callable)` | `NodeList` (chainable) |
| `map(callable)` | `array` |
| `filter(callable)` | `NodeList` |
| `first()` | `Element\|null` |
| `last()` | `Element\|null` |
| `toArray()` | `Element[]` |
| `count()` | `int` |

---

## Examples

### Reading attributes and content

```php
$dom = Document::from( $html );

$first_img = $dom->query( 'img' );

if ( $first_img ) {
    $src = $first_img->attr( 'src' );
    $alt = $first_img->attr( 'alt' );
}
```

### Mutating attributes (chainable)

```php
$dom->queryAll( 'img' )->each( function ( $img ) {
    $img->attr( 'loading', 'lazy' )
        ->attr( 'decoding', 'async' )
        ->addClass( 'js-lazy' );
} );

echo $dom->toHtml();
```

### Replacing an element

```php
// Swap an element with new markup.
$dom->query( '.legacy-embed' )?->replaceWithHtml(
    '<aside class="notice">Embed removed — see original post.</aside>'
);

// Or with plain text (no HTML parsing).
$dom->query( '.profanity' )?->replaceWithText( '****' );
```

### Inserting markup around elements

```php
$dom->query( 'h2.section' )
    ?->insertBeforeHtml( '<hr class="section-rule">' )
    ->insertAfterHtml( '<p class="section-meta">Last updated today.</p>' );

$dom->query( 'figure.hero' )?->wrapWith( '<div class="hero-frame"></div>' );
```

### Inner/outer manipulation

```php
$h1 = $dom->query( 'h1' );

if ( $h1 ) {
    $existing  = $h1->text();
    $h1->text( "$existing — Updated" );

    // Replace innerHTML wholesale.
    $h1->html( '<span class="eyebrow">News</span> ' . esc_html( $existing ) );
}
```

### Removing elements

```php
$dom->queryAll( 'script, style, noscript' )->each( fn( $el ) => $el->remove() );
```

### Filtering then operating

```php
$external_links = $dom->queryAll( 'a' )->filter( function ( $a ) {
    $href = $a->attr( 'href' );
    return str_starts_with( $href, 'http' ) && ! str_contains( $href, 'example.com' );
} );

$external_links->each( function ( $a ) {
    $a->attr( 'target', '_blank' )
      ->attr( 'rel', 'noopener noreferrer' );
} );
```

### Walking the tree with a custom callback

```php
use Mai\DOM\Document;
use Dom\Text;

$dom = Document::from( $html );

Document::walkNodes( $dom->unwrap()->body, function ( $node ) {
    if ( $node instanceof Text ) {
        // Custom text-node mutation, e.g. replace smart quotes.
        $node->textContent = str_replace( [ '“', '”' ], '"', $node->textContent );
    }
} );

echo $dom->toHtml();
```

### Minify whitespace

```php
echo Document::from( $verbose_html )->minify()->toHtml();
```

---

## Real-world WordPress recipes

### `the_content` filter — lazy-load all images

```php
use Mai\DOM\Document;

add_filter( 'the_content', function ( $content ) {
    if ( ! is_singular() ) {
        return $content;
    }

    $dom = Document::from( $content );

    $dom->queryAll( 'img:not([loading])' )->each( function ( $img ) {
        $img->attr( 'loading', 'lazy' )->attr( 'decoding', 'async' );
    } );

    return $dom->toHtml();
} );
```

### `the_content` filter — strip legacy Facebook embeds

```php
add_filter( 'the_content', function ( $content ) {
    if ( ! str_contains( $content, 'facebook.com' ) ) {
        return $content;
    }

    $dom = Document::from( $content );

    $dom->queryAll( 'iframe[src*="facebook.com"], blockquote.fb-post' )
        ->each( fn( $el ) => $el->replaceWithHtml(
            '<p class="legacy-embed">[Facebook embed removed]</p>'
        ) );

    return $dom->toHtml();
} );
```

### `render_block` filter — add target=_blank to off-site links inside post content

```php
add_filter( 'render_block_core/post-content', function ( $html, $block ) {
    $dom = Document::from( $html );

    $dom->queryAll( 'a[href^="http"]' )
        ->filter( fn( $a ) => ! str_contains( $a->attr( 'href' ), home_url() ) )
        ->each( fn( $a ) => $a->attr( 'target', '_blank' )->attr( 'rel', 'noopener noreferrer' ) );

    return $dom->toHtml();
}, 10, 2 );
```

### WP-CLI migration command — re-runnable cleanup of legacy markup

```php
use Mai\DOM\Document;
use WP_CLI;

WP_CLI::add_command( 'acme fix-legacy-embeds', function () {
    $ids = get_posts( [
        'post_type'      => 'post',
        'post_status'    => 'publish',
        's'              => 'facebook.com',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ] );

    $changed = 0;

    foreach ( $ids as $id ) {
        $post = get_post( $id );
        $dom  = Document::from( $post->post_content );

        $matches = $dom->queryAll( 'iframe[src*="facebook.com"]' );

        if ( ! count( $matches ) ) {
            continue;
        }

        $matches->each( fn( $el ) => $el->replaceWithHtml(
            '<p class="legacy-embed">[Facebook embed removed]</p>'
        ) );

        wp_update_post( [
            'ID'           => $id,
            'post_content' => $dom->toHtml(),
        ] );

        $changed++;
        WP_CLI::log( "Fixed #{$id}" );
    }

    WP_CLI::success( "Done. {$changed} posts updated." );
} );
```

Idempotent and re-runnable: a fresh DB snapshot won't have the cleaned posts; running the command again finishes them.

### Excerpt — extract first paragraph cleanly

```php
add_filter( 'get_the_excerpt', function ( $excerpt, $post ) {
    if ( $excerpt ) {
        return $excerpt;
    }

    $first_p = Document::from( $post->post_content )->query( 'p' );

    return $first_p ? wp_strip_all_tags( $first_p->html() ) : '';
}, 10, 2 );
```

### Strip dangerous nodes from user-submitted HTML

```php
$dom = Document::from( $user_html );

$dom->queryAll( 'script, style, iframe, object, embed' )
    ->each( fn( $el ) => $el->remove() );

$dom->queryAll( '[onclick], [onload], [onerror]' )->each( function ( $el ) {
    foreach ( [ 'onclick', 'onload', 'onerror' ] as $attr ) {
        $el->removeAttr( $attr );
    }
} );

$clean = $dom->toHtml();
```

---

## Versioned coexistence (advanced)

When more than one plugin on the same WP install bundles `mai-dom`, all versions register themselves with `Mai_DOM_Bootstrap`. On first request for any `Mai\DOM\*` class, the autoloader picks the highest registered version and resolves the class file from that version's `src/` directory.

```
Plugin A (vendor/maithemewp/mai-dom @ 0.1.0)
Plugin B (vendor/maithemewp/mai-dom @ 0.2.0)
                  │
                  ▼
       Both register on autoload
                  │
                  ▼
       First Mai\DOM\Document request
                  │
                  ▼
       Autoloader picks 0.2.0's src/
                  │
                  ▼
       Both plugins use 0.2.0
```

**Bootstrap protocol is frozen.** Never change `Mai_DOM_Bootstrap::register()`'s signature — old bundled copies in the wild will call the original signature on whichever bootstrap loaded first.

---

## License

GPL-2.0-or-later
