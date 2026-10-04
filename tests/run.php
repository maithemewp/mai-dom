<?php
/**
 * Every public method, checked with nothing installed.
 *
 *   php tests/run.php
 *
 * Any PHP warning or notice fails the run. `children()` once read a property
 * that only exists from PHP 8.5 and returned nothing on 8.4, with only a
 * warning to show for it, so run this on 8.4 as well as the newest PHP.
 */

declare( strict_types=1 );

spl_autoload_register( static function ( string $class ): void {
	if ( str_starts_with( $class, 'Mai\\DOM\\' ) ) {
		require __DIR__ . '/../src/' . substr( $class, strlen( 'Mai\\DOM\\' ) ) . '.php';
	}
} );

set_error_handler( static function ( int $level, string $message, string $file, int $line ): never {
	throw new ErrorException( $message, 0, $level, $file, $line );
} );

use Mai\DOM\Document;
use Mai\DOM\Element;
use Mai\DOM\NodeList;

$passed = 0;
$failed = [];

function check( string $what, callable $test, mixed $expected ): void {
	global $passed, $failed;

	try {
		$actual = $test();
	} catch ( Throwable $e ) {
		$actual = get_class( $e ) . ': ' . $e->getMessage();
	}

	if ( $expected === $actual ) {
		$passed++;
		echo "  ok   $what\n";

		return;
	}

	$failed[] = $what;
	printf( "  FAIL %s\n       expected %s, got %s\n", $what, var_export( $expected, true ), var_export( $actual, true ) );
}

/** A fresh list to work on. */
function doc(): Document {
	return Document::from( '<ul class="list"><li class="a">One</li><li class="b">Two</li>text<li>Three</li></ul><p id="p">Hi <b>there</b></p>' );
}

echo "\nPHP " . PHP_VERSION . "\n\nDocument\n";
check( 'from() and toHtml() round-trip a fragment', fn() => Document::from( '<p>Hi</p>' )->toHtml(), '<p>Hi</p>' );
check( 'unwrap() is the HTMLDocument', fn() => doc()->unwrap() instanceof Dom\HTMLDocument, true );
check( 'query() finds the first match', fn() => doc()->query( 'li' )?->text(), 'One' );
check( 'query() is null without a match', fn() => doc()->query( 'table' ), null );
check( 'queryAll() finds every match', fn() => doc()->queryAll( 'li' )->count(), 3 );
check( 'minify() collapses whitespace in text', fn() => Document::from( "<p>a   \n  b</p>" )->minify()->toHtml(), '<p>a b</p>' );
check( 'walkNodes() visits every node', function (): int {
	$count = 0;
	Document::walkNodes( Document::from( '<p>a<b>c</b></p>' )->unwrap()->body, function () use ( &$count ): void {
		$count++;
	} );

	return $count;
}, 5 );

echo "\nElement, reading\n";
check( 'unwrap() is the node', fn() => doc()->query( 'ul' )->unwrap() instanceof Dom\Element, true );
check( 'query() inside an element', fn() => doc()->query( 'ul' )->query( '.b' )?->text(), 'Two' );
check( 'queryAll() inside an element', fn() => doc()->query( 'ul' )->queryAll( 'li' )->count(), 3 );
check( 'tag() is lowercase', fn() => doc()->query( 'ul' )->tag(), 'ul' );
check( 'attr() reads', fn() => doc()->query( 'p' )->attr( 'id' ), 'p' );
check( 'attr() reads a missing attribute as empty', fn() => doc()->query( 'p' )->attr( 'title' ), '' );
check( 'hasClass()', fn() => [ doc()->query( 'li' )->hasClass( 'a' ), doc()->query( 'li' )->hasClass( 'z' ) ], [ true, false ] );
check( 'html() reads inner HTML', fn() => doc()->query( 'p' )->html(), 'Hi <b>there</b>' );
check( 'text() reads text', fn() => doc()->query( 'p' )->text(), 'Hi there' );
check( 'outerHtml()', fn() => doc()->query( 'b' )->outerHtml(), '<b>there</b>' );
check( '__toString() is outer HTML', fn() => (string) doc()->query( 'b' ), '<b>there</b>' );
check( 'parent()', fn() => doc()->query( 'b' )->parent()?->attr( 'id' ), 'p' );
check( 'children() lists element children only, in order', fn() => doc()->query( 'ul' )->children()->map( fn( Element $e ): string => $e->text() ), [ 'One', 'Two', 'Three' ] );
check( 'children() of an empty element is empty', fn() => Document::from( '<div></div>' )->query( 'div' )->children()->count(), 0 );

echo "\nElement, changing\n";
check( 'attr() sets', fn() => doc()->query( 'p' )->attr( 'title', 'T' )->attr( 'title' ), 'T' );
check( 'removeAttr()', fn() => doc()->query( 'p' )->removeAttr( 'id' )->outerHtml(), '<p>Hi <b>there</b></p>' );
check( 'addClass() and removeClass()', fn() => doc()->query( 'li' )->addClass( 'x', 'y' )->removeClass( 'a' )->attr( 'class' ), 'x y' );
check( 'toggleClass()', fn() => doc()->query( 'li' )->toggleClass( 'a' )->toggleClass( 'z' )->attr( 'class' ), 'z' );
check( 'html() sets', fn() => doc()->query( 'p' )->html( '<i>new</i>' )->outerHtml(), '<p id="p"><i>new</i></p>' );
check( 'text() sets, escaped', fn() => doc()->query( 'p' )->text( '<b>' )->outerHtml(), '<p id="p">&lt;b&gt;</p>' );
check( 'appendHtml()', fn() => doc()->query( 'p' )->appendHtml( '<i>!</i>' )->html(), 'Hi <b>there</b><i>!</i>' );
check( 'prependHtml()', fn() => doc()->query( 'p' )->prependHtml( '<i>!</i>' )->html(), '<i>!</i>Hi <b>there</b>' );
check( 'beforeHtml()', function (): string {
	$d = doc();
	$d->query( 'p' )->beforeHtml( '<hr>' );

	return $d->query( 'hr' )?->unwrap()->nextElementSibling?->getAttribute( 'id' );
}, 'p' );
check( 'afterHtml()', function (): string {
	$d = doc();
	$d->query( '.a' )->afterHtml( '<li class="new">New</li>' );

	return implode( ',', $d->query( 'ul' )->children()->map( fn( Element $e ): string => $e->text() ) );
}, 'One,New,Two,Three' );
check( 'replaceWithHtml()', function (): string {
	$d = doc();
	$d->query( 'b' )->replaceWithHtml( '<em>you</em>' );

	return $d->query( 'p' )->html();
}, 'Hi <em>you</em>' );
check( 'replaceWithText()', function (): string {
	$d = doc();
	$d->query( 'b' )->replaceWithText( '<you>' );

	return $d->query( 'p' )->html();
}, 'Hi &lt;you&gt;' );
check( 'wrapWith()', function (): string {
	$d = doc();
	$d->query( 'b' )->wrapWith( '<span class="w"></span>' );

	return $d->query( 'p' )->html();
}, 'Hi <span class="w"><b>there</b></span>' );
check( 'remove()', function (): int {
	$d = doc();
	$d->query( '.a' )->remove();

	return $d->queryAll( 'li' )->count();
}, 2 );

echo "\nNodeList\n";
check( 'first() and last()', fn() => [ doc()->queryAll( 'li' )->first()?->text(), doc()->queryAll( 'li' )->last()?->text() ], [ 'One', 'Three' ] );
check( 'first() of an empty list is null', fn() => ( new NodeList( [] ) )->first(), null );
check( 'map()', fn() => doc()->queryAll( 'li' )->map( fn( Element $e ): string => $e->tag() ), [ 'li', 'li', 'li' ] );
check( 'filter()', fn() => doc()->queryAll( 'li' )->filter( fn( Element $e ): bool => $e->hasClass( 'b' ) )->count(), 1 );
check( 'each() visits every element and returns the list', function (): array {
	$seen = [];
	$list = doc()->queryAll( 'li' );
	$back = $list->each( function ( Element $e ) use ( &$seen ): void {
		$seen[] = $e->text();
	} );

	return [ $seen, $back === $list ];
}, [ [ 'One', 'Two', 'Three' ], true ] );
check( 'toArray() and iteration agree', function (): bool {
	$list = doc()->queryAll( 'li' );

	return $list->toArray() === iterator_to_array( $list );
}, true );

printf( "\n%d passed, %d failed\n\n", $passed, count( $failed ) );
exit( [] === $failed ? 0 : 1 );
