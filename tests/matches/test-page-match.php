<?php

require dirname( __FILE__ ) . '/../../matches/page.php';

class PageMatchTest extends WP_UnitTestCase {
	private function set_404( $is_404 ) {
		global $wp_query;

		wp_reset_query();
		set_query_var( 'is_404', $is_404 );

		$wp_query->is_404 = $is_404;
	}

	public function testNoData() {
		$match = new Page_Match();
		$saved = array(
			'url' => '',
			'page' => '404',
		);
		$this->assertEquals( $saved, $match->save( array( 'bad' => 'thing' ) ) );
	}

	public function testBadPage() {
		$match = new Page_Match();
		$saved = array(
			'url' => '',
			'page' => '404',
		);
		$this->assertEquals( $saved, $match->save( array( 'page' => '505' ) ) );
	}

	public function testGoodPage() {
		$match = new Page_Match();
		$saved = array(
			'url' => '',
			'page' => '404',
		);
		$this->assertEquals( $saved, $match->save( array( 'page' => '404' ) ) );
	}

	public function testLoadBad() {
		$match = new Page_Match();
		$match->load( serialize( array( 'url' => 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', 'page' => '' ) ) );
		$this->assertEquals( 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', $match->url );
	}

	public function testMatch404() {
		$this->set_404( true );

		$match = new Page_Match( serialize( array( 'page' => '404', 'url' => '/target' ) ) );
		$this->assertTrue( $match->is_match( '' ) );
	}

	public function testNotMatch() {
		$this->set_404( false );

		$match = new Page_Match( serialize( array( 'page' => '404', 'url' => '/cat' ) ) );
		$this->assertFalse( $match->is_match( '' ) );
	}
}
