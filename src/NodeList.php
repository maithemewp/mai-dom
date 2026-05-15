<?php
/**
 * Mai\DOM\NodeList — iterable collection of Element wrappers.
 *
 * @package maithemewp/mai-dom
 * @license GPL-2.0-or-later
 */

namespace Mai\DOM;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Iterator;

defined( 'ABSPATH' ) || exit;

/**
 * Iterable wrapper for a list of Element objects.
 *
 * Returned by Document::queryAll() and Element::queryAll() / children().
 */
class NodeList implements IteratorAggregate, Countable {

	/**
	 * @var Element[]
	 */
	private array $elements;

	/**
	 * @param Element[] $elements
	 */
	public function __construct( array $elements ) {
		$this->elements = array_values( $elements );
	}

	/**
	 * Run a callback for each element. Chainable.
	 *
	 * @param callable $callback fn(Element $element, int $index)
	 *
	 * @return self
	 */
	public function each( callable $callback ): self {
		foreach ( $this->elements as $index => $element ) {
			$callback( $element, $index );
		}

		return $this;
	}

	/**
	 * Map each element through a callback, returning an array.
	 *
	 * @param callable $callback fn(Element $element, int $index): mixed
	 *
	 * @return array
	 */
	public function map( callable $callback ): array {
		$out = [];

		foreach ( $this->elements as $index => $element ) {
			$out[] = $callback( $element, $index );
		}

		return $out;
	}

	/**
	 * Filter to a new NodeList of elements where the callback returns truthy.
	 *
	 * @param callable $callback fn(Element $element, int $index): bool
	 *
	 * @return self
	 */
	public function filter( callable $callback ): self {
		$out = [];

		foreach ( $this->elements as $index => $element ) {
			if ( $callback( $element, $index ) ) {
				$out[] = $element;
			}
		}

		return new self( $out );
	}

	/**
	 * Get the first Element, or null if empty.
	 *
	 * @return Element|null
	 */
	public function first(): ?Element {
		return $this->elements[0] ?? null;
	}

	/**
	 * Get the last Element, or null if empty.
	 *
	 * @return Element|null
	 */
	public function last(): ?Element {
		return $this->elements[ array_key_last( $this->elements ) ] ?? null;
	}

	/**
	 * Return the underlying array of Element objects.
	 *
	 * @return Element[]
	 */
	public function toArray(): array {
		return $this->elements;
	}

	/**
	 * @return Iterator<int,Element>
	 */
	public function getIterator(): Iterator {
		return new ArrayIterator( $this->elements );
	}

	/**
	 * @return int
	 */
	public function count(): int {
		return count( $this->elements );
	}
}
