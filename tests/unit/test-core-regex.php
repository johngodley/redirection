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

	public function testCaptureCannotUseShortcode() {
		$regex = new Regex( '^/old/(.*)$' );

		$this->assertEquals( '/new/%5Bupper%5Dpage%5B/upper%5D', $regex->replace( '/new/$1', '/old/[upper]page[/upper]' ) );
		$this->assertEquals( '/new/%5Bupper%5Dpage%5B/upper%5D', $regex->replace( '/new/${1}', '/old/[upper]page[/upper]' ) );
		$this->assertEquals( '/new/%5Bupper%5Dpage%5B/upper%5D', $regex->replace( '/new/\1', '/old/[upper]page[/upper]' ) );
		$this->assertEquals( '/new//old/%5Bupper%5D', $regex->replace( '/new/$0', '/old/[upper]' ) );
	}

	public function testCaptureCannotUseDeprecatedTag() {
		$regex = new Regex( '^/old/(.*)$' );

		$this->assertEquals( '/new/%25userid%25/%25userlogin%25/%25userurl%25', $regex->replace( '/new/$1', '/old/%userid%/%userlogin%/%userurl%' ) );
	}

	public function testUnmatchedTextCannotUseShortcode() {
		$regex = new Regex( 'old/(.*)' );

		$this->assertEquals( '/%5Bupper%5Dx%5B/upper%5Dnew/page', $regex->replace( 'new/$1', '/[upper]x[/upper]old/page' ) );
	}

	public function testReplacePatternCanUseShortcode() {
		$regex = new Regex( '^/old/(.*)$' );

		$this->assertEquals( '/[lower]Page[/lower]/[userid]/%userid%', $regex->replace( '/[lower]$1[/lower]/[userid]/%userid%', '/old/Page' ) );
	}
}
