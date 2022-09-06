<?php

use Redirection\Match;

require_once dirname( __FILE__ ) . '/../../includes/match/match-url.php';

class MatchesTest extends WP_UnitTestCase {
	public function testRemoveNewline() {
		$match = new Match\Url_Only();

		$this->assertEquals( $match->sanitize_url( "/some/url\nsomethingelse1" ), '/some/url' );
		$this->assertEquals( $match->sanitize_url( "/some/url\rsomethingelse2" ), '/some/url' );
		$this->assertEquals( $match->sanitize_url( "/some/url\r\nsomethingelse3" ), '/some/url' );
	}
}
