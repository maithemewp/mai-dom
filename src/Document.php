<?php
/**
 * Mai\DOM\Document — wrapper around PHP 8.4's Dom\HTMLDocument.
 *
 * @package maithemewp/mai-dom
 * @license GPL-2.0-or-later
 */

namespace Mai\DOM;

use Dom\HTMLDocument;
use Dom\Node;
use Dom\Text;

defined( 'ABSPATH' ) || exit;

/**
 * HTML document wrapper.
 *
 * Provides ergonomic, chainable methods for parsing and manipulating HTML.
 * Wraps PHP 8.4's Dom\HTMLDocument under the hood.
 *
 * @since 0.1.0
 */
class Document {

	/**
	 * The underlying HTMLDocument.
	 *
	 * @since 0.1.0
	 *
	 * @var HTMLDocument
	 */
	private HTMLDocument $dom;

	/**
	 * Constructor — use Document::from() in most cases.
	 *
	 * @since 0.1.0
	 *
	 * @param HTMLDocument $dom
	 */
	public function __construct( HTMLDocument $dom ) {
		$this->dom = $dom;
	}

	/**
	 * Create a Document from an HTML string.
	 *
	 * The input is wrapped in a minimal <html>/<body> shell so fragments
	 * (the common case for filters like the_content and render_block) parse
	 * correctly. toHtml() returns just the body's innerHTML, so the shell
	 * is invisible to callers.
	 *
	 * @since 0.1.0
	 *
	 * @param string $html
	 *
	 * @return self
	 */
	public static function from( string $html ): self {
		$wrapped = sprintf(
			'<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>%s</body></html>',
			$html
		);

		return new self( HTMLDocument::createFromString( $wrapped, LIBXML_HTML_NOIMPLIED ) );
	}

	/**
	 * Get the underlying HTMLDocument (escape hatch).
	 *
	 * @since 0.1.0
	 *
	 * @return HTMLDocument
	 */
	public function unwrap(): HTMLDocument {
		return $this->dom;
	}

	/**
	 * Find the first element matching a CSS selector.
	 *
	 * @since 0.1.0
	 *
	 * @param string $selector
	 *
	 * @return Element|null
	 */
	public function query( string $selector ): ?Element {
		$node = $this->dom->body->querySelector( $selector );

		return $node ? new Element( $node ) : null;
	}

	/**
	 * Find all elements matching a CSS selector.
	 *
	 * @since 0.1.0
	 *
	 * @param string $selector
	 *
	 * @return NodeList
	 */
	public function queryAll( string $selector ): NodeList {
		$nodes    = $this->dom->body->querySelectorAll( $selector );
		$elements = [];

		foreach ( $nodes as $node ) {
			$elements[] = new Element( $node );
		}

		return new NodeList( $elements );
	}

	/**
	 * Get the body innerHTML.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function toHtml(): string {
		return trim( $this->dom->body->innerHTML );
	}

	/**
	 * Collapse repeated whitespace in text nodes in place.
	 *
	 * @since 0.1.0
	 *
	 * @return self
	 */
	public function minify(): self {
		self::walkNodes( $this->dom->body, function ( Node $node ): void {
			if ( $node instanceof Text ) {
				$node->textContent = preg_replace( '/\s+/', ' ', $node->textContent );
			}
		} );

		return $this;
	}

	/**
	 * Recursively walk every node under (and including) $node.
	 *
	 * @since 0.1.0
	 *
	 * @param Node     $node
	 * @param callable $callback Receives each Node.
	 *
	 * @return void
	 */
	public static function walkNodes( Node $node, callable $callback ): void {
		$callback( $node );

		foreach ( $node->childNodes as $child ) {
			self::walkNodes( $child, $callback );
		}
	}
}
