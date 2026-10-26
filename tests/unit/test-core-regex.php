<?php

require_once PLUGIN_PATH . '/includes/core/class-regex.php';

use Redirection\Core\Regex;

class CoreRegexTest extends TestCase {
	public function testReplaceIsSameAsPregReplace() {
		$patterns = [ '^/old/(.*)$', 'old/(.*)', '(a)(b)?', '^/(\d+)/(\w+)$', 'x*', '(cat)(\d)', '(o)' ];
		$replacements = [ '/new/$1', '${1}x', '\1\2', '$0', '\\\\$1', '\$1', '$12', '${1', '$', 'a\b', '$2$1', '\\\\\1', '${2}${1}', '$99', '' ];
		$subjects = [ '/old/page', '/foo/old/bar', 'ab', 'a', '/12/abc', 'cat5', '', 'boo' ];

		foreach ( $patterns as $pattern ) {
			$regex = new Regex( $pattern );

			foreach ( $replacements as $replacement ) {
				foreach ( $subjects as $subject ) {
					$expected = preg_replace( '@' . $pattern . '@s', $replacement, $subject );

					$this->assertSame( $expected, $regex->replace( $replacement, $subject ), $pattern . ' ' . $replacement . ' ' . $subject );
				}
			}
		}
	}

	public function testReplaceInvalidRegexReturnsTarget() {
		$regex = new Regex( '/cat[5' );

		$this->assertEquals( 'cat5', $regex->replace( 'dog$1', 'cat5' ) );
	}

	public function testReplaceStillEncodesSpaces() {
		$regex = new Regex( 'cat(.*)' );

		$this->assertEquals( 'dog5%203?thing+4', $regex->replace( 'dog$1', 'cat5 3?thing 4' ) );
	}

	public function testCaptureKeepsBrackets() {
		$regex = new Regex( '^/old/(.*)$' );

		$this->assertEquals( '/new/x?param[]=1', $regex->replace( '/new/$1', '/old/x?param[]=1' ) );
	}

	public function testCaptureUsesPlaceholders() {
		$regex = new Regex( '^/old/(.*)$' );
		$placeholders = [ '[' => '<', ']' => '>' ];

		$this->assertEquals( '/new/<upper>page</upper>', $regex->replace( '/new/$1', '/old/[upper]page[/upper]', true, $placeholders ) );
		$this->assertEquals( '/new/<upper>page</upper>', $regex->replace( '/new/${1}', '/old/[upper]page[/upper]', true, $placeholders ) );
		$this->assertEquals( '/new/<upper>page</upper>', $regex->replace( '/new/\1', '/old/[upper]page[/upper]', true, $placeholders ) );
		$this->assertEquals( '/new//old/<upper>', $regex->replace( '/new/$0', '/old/[upper]', true, $placeholders ) );
	}

	public function testUnmatchedTextUsesPlaceholders() {
		$regex = new Regex( 'old/(.*)' );

		$this->assertEquals( '/<upper>x</upper>new/page', $regex->replace( 'new/$1', '/[upper]x[/upper]old/page', true, [ '[' => '<', ']' => '>' ] ) );
	}

	public function testReplacePatternDoesNotUsePlaceholders() {
		$regex = new Regex( '^/old/(.*)$' );

		$this->assertEquals( '/[lower]<x></x>[/lower]', $regex->replace( '/[lower]$1[/lower]', '/old/[x][/x]', true, [ '[' => '<', ']' => '>' ] ) );
	}

	public function testReplaceCanDropTextBeforeMatch() {
		$regex = new Regex( '/old/(.*)' );

		$this->assertEquals( '/x/new/page', $regex->replace( '/new/$1', '/x/old/page' ) );
		$this->assertEquals( '/new/page', $regex->replace( '/new/$1', '/x/old/page', false ) );
		$this->assertEquals( '/new/page/x', $regex->replace( '/new/$1', '/x/old/page/x', false ) );
		$this->assertEquals( '/x/y', $regex->replace( '/new/$1', '/x/y', false ) );
	}

	public function testReplacePatternCanUseShortcode() {
		$regex = new Regex( '^/old/(.*)$' );

		$this->assertEquals( '/[lower]Page[/lower]/[userid]', $regex->replace( '/[lower]$1[/lower]/[userid]', '/old/Page' ) );
	}
}
