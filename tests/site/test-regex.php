<?php

use Redirection\Site;

class RegexTest extends WP_UnitTestCase {
	public function testPlainRegex() {
		$regex = new Site\Regex( 'cat.*' );

		$this->assertTrue( $regex->is_match( 'cat5' ) );
		$this->assertFalse( $regex->is_match( 'dog5' ) );
	}

	public function testMalformedRegex() {
		$regex = new Site\Regex( '/cat[5' );

		$this->assertFalse( $regex->is_match( 'cat5' ) );
	}

	public function testCaseRegex() {
		$regex = new Site\Regex( 'cat.*', true );

		$this->assertTrue( $regex->is_match( 'CAT5' ) );
		$this->assertFalse( $regex->is_match( 'DOG5' ) );
	}

	public function testDecode() {
		$regex = new Site\Regex( urlencode( 'für.*' ) );

		$this->assertTrue( $regex->is_match( 'fürcat' ) );
	}

	public function testQuoteRegex() {
		$regex = new Site\Regex( '@cat.*' );

		$this->assertTrue( $regex->is_match( '@cat5' ) );
		$this->assertFalse( $regex->is_match( '@dog5' ) );
	}

	public function testReplace() {
		$regex = new Site\Regex( 'cat(.*)' );

		$this->assertEquals( 'dog5', $regex->replace( 'dog$1', 'cat5' ) );
	}

	public function testPathSpaceReplace() {
		$regex = new Site\Regex( 'cat(.*)' );

		$this->assertEquals( 'dog5%203', $regex->replace( 'dog$1', 'cat5 3' ) );
	}

	public function testQuerySpaceReplace() {
		$regex = new Site\Regex( 'cat(.*)' );

		$this->assertEquals( 'dog5?thing+4', $regex->replace( 'dog$1', 'cat5?thing 4' ) );
	}

	public function testMalformedReplace() {
		$regex = new Site\Regex( '/cat[5' );

		$this->assertEquals( 'cat5', $regex->replace( 'dog$1', 'cat5' ) );
	}

	public function testPlusRegex() {
		$regex = new Site\Regex( '^/hello\+world' );

		$this->assertTrue( $regex->is_match( '/hello+world' ) );
	}
}
