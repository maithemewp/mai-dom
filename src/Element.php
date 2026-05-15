<?php
/**
 * Mai\DOM\Element — wrapper around a single DOM element.
 *
 * @package maithemewp/mai-dom
 * @license GPL-2.0-or-later
 */

namespace Mai\DOM;

use Dom\Element as DomElement;
use Dom\HTMLElement;

defined( 'ABSPATH' ) || exit;

/**
 * Single-element wrapper with chainable, jQuery-flavored helpers.
 */
class Element {

	/**
	 * The underlying DOM element.
	 *
	 * @var HTMLElement|DomElement
	 */
	private HTMLElement|DomElement $node;

	/**
	 * Constructor.
	 *
	 * @param HTMLElement|DomElement $node
	 */
	public function __construct( HTMLElement|DomElement $node ) {
		$this->node = $node;
	}

	/**
	 * Get the underlying DOM node (escape hatch).
	 *
	 * @return HTMLElement|DomElement
	 */
	public function unwrap(): HTMLElement|DomElement {
		return $this->node;
	}

	/**
	 * Find the first descendant matching a CSS selector.
	 *
	 * @param string $selector
	 *
	 * @return self|null
	 */
	public function query( string $selector ): ?self {
		$node = $this->node->querySelector( $selector );

		return $node ? new self( $node ) : null;
	}

	/**
	 * Find all descendants matching a CSS selector.
	 *
	 * @param string $selector
	 *
	 * @return NodeList
	 */
	public function queryAll( string $selector ): NodeList {
		$nodes    = $this->node->querySelectorAll( $selector );
		$elements = [];

		foreach ( $nodes as $node ) {
			$elements[] = new self( $node );
		}

		return new NodeList( $elements );
	}

	/**
	 * Get the element's tag name (lowercase).
	 *
	 * @return string
	 */
	public function tag(): string {
		return strtolower( $this->node->tagName );
	}

	/**
	 * Get or set an attribute.
	 *
	 * @param string      $name  Attribute name.
	 * @param string|null $value If provided, sets the attribute. Otherwise gets it.
	 *
	 * @return string|self String when getting, self when setting.
	 */
	public function attr( string $name, ?string $value = null ): string|self {
		if ( null === $value ) {
			return (string) $this->node->getAttribute( $name );
		}

		$this->node->setAttribute( $name, $value );

		return $this;
	}

	/**
	 * Remove an attribute.
	 *
	 * @param string $name
	 *
	 * @return self
	 */
	public function removeAttr( string $name ): self {
		$this->node->removeAttribute( $name );

		return $this;
	}

	/**
	 * Check whether the element has a class.
	 *
	 * @param string $class
	 *
	 * @return bool
	 */
	public function hasClass( string $class ): bool {
		return $this->node->classList->contains( $class );
	}

	/**
	 * Add one or more classes.
	 *
	 * @param string ...$classes
	 *
	 * @return self
	 */
	public function addClass( string ...$classes ): self {
		foreach ( $classes as $class ) {
			$this->node->classList->add( $class );
		}

		return $this;
	}

	/**
	 * Remove one or more classes.
	 *
	 * @param string ...$classes
	 *
	 * @return self
	 */
	public function removeClass( string ...$classes ): self {
		foreach ( $classes as $class ) {
			$this->node->classList->remove( $class );
		}

		return $this;
	}

	/**
	 * Toggle a class.
	 *
	 * @param string $class
	 *
	 * @return self
	 */
	public function toggleClass( string $class ): self {
		$this->node->classList->toggle( $class );

		return $this;
	}

	/**
	 * Get or set innerHTML.
	 *
	 * @param string|null $html If provided, sets innerHTML. Otherwise gets it.
	 *
	 * @return string|self
	 */
	public function html( ?string $html = null ): string|self {
		if ( null === $html ) {
			return $this->node->innerHTML;
		}

		$this->node->innerHTML = $html;

		return $this;
	}

	/**
	 * Get or set textContent.
	 *
	 * @param string|null $text
	 *
	 * @return string|self
	 */
	public function text( ?string $text = null ): string|self {
		if ( null === $text ) {
			return $this->node->textContent;
		}

		$this->node->textContent = $text;

		return $this;
	}

	/**
	 * Get the outerHTML (the element itself plus its descendants).
	 *
	 * @return string
	 */
	public function outerHtml(): string {
		return $this->node->ownerDocument->saveHTML( $this->node );
	}

	/**
	 * Append HTML inside this element (as the last child).
	 *
	 * @param string $html
	 *
	 * @return self
	 */
	public function append( string $html ): self {
		$this->node->insertAdjacentHTML( 'beforeend', $html );

		return $this;
	}

	/**
	 * Prepend HTML inside this element (as the first child).
	 *
	 * @param string $html
	 *
	 * @return self
	 */
	public function prepend( string $html ): self {
		$this->node->insertAdjacentHTML( 'afterbegin', $html );

		return $this;
	}

	/**
	 * Insert HTML immediately before this element (as a previous sibling).
	 *
	 * @param string $html
	 *
	 * @return self
	 */
	public function insertBeforeHtml( string $html ): self {
		$this->node->insertAdjacentHTML( 'beforebegin', $html );

		return $this;
	}

	/**
	 * Insert HTML immediately after this element (as a next sibling).
	 *
	 * @param string $html
	 *
	 * @return self
	 */
	public function insertAfterHtml( string $html ): self {
		$this->node->insertAdjacentHTML( 'afterend', $html );

		return $this;
	}

	/**
	 * Replace this element with an HTML string.
	 *
	 * After calling this the element is detached from the DOM; further
	 * mutations on this wrapper have no visible effect.
	 *
	 * @param string $html
	 *
	 * @return void
	 */
	public function replaceWithHtml( string $html ): void {
		$this->node->insertAdjacentHTML( 'beforebegin', $html );
		$this->node->remove();
	}

	/**
	 * Replace this element with a plain text string (no HTML parsing).
	 *
	 * @param string $text
	 *
	 * @return void
	 */
	public function replaceWithText( string $text ): void {
		$this->node->replaceWith( $text );
	}

	/**
	 * Wrap this element inside a new HTML container.
	 *
	 * The wrapper HTML's outermost element becomes the new parent; if it
	 * already has children they are preserved and this element is appended.
	 *
	 * @param string $html Wrapper HTML, e.g. '<div class="card"></div>'.
	 *
	 * @return self
	 */
	public function wrapWith( string $html ): self {
		$doc = $this->node->ownerDocument;

		// Parse the wrapper HTML into a detached div, take its first element.
		$temp = $doc->createElement( 'div' );
		$temp->innerHTML = $html;
		$wrapper = $temp->firstElementChild;

		if ( ! $wrapper ) {
			return $this;
		}

		// Place wrapper where this node lives, then move this node inside.
		$this->node->parentNode->insertBefore( $wrapper, $this->node );
		$wrapper->appendChild( $this->node );

		return $this;
	}

	/**
	 * Remove this element from the DOM.
	 *
	 * @return void
	 */
	public function remove(): void {
		$this->node->remove();
	}

	/**
	 * Get the parent element (or null at the root).
	 *
	 * @return self|null
	 */
	public function parent(): ?self {
		$parent = $this->node->parentNode;

		if ( ! $parent instanceof HTMLElement && ! $parent instanceof DomElement ) {
			return null;
		}

		return new self( $parent );
	}

	/**
	 * Get the element's direct element children (excludes text nodes).
	 *
	 * @return NodeList
	 */
	public function children(): NodeList {
		$elements = [];

		foreach ( $this->node->children as $child ) {
			$elements[] = new self( $child );
		}

		return new NodeList( $elements );
	}

	/**
	 * Echoing an Element returns its outerHTML.
	 *
	 * @return string
	 */
	public function __toString(): string {
		return $this->outerHtml();
	}
}
