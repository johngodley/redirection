<?php

require_once dirname( __FILE__ ) . '/../../matches/custom-filter.php';

class CustomMatchTest extends WP_UnitTestCase {
	public function testTargetSanitized() {
		$match = new Custom_Match();
		$saved = array(
			'url_from' => '/some/url',
			'url_notfrom' => '/some/url',
			'filter' => 'filterthing',
		);
		$source = array(
			'url_from' => "/some/url\nsomethingelse1",
			'url_notfrom' => "/some/url\nsomethingelse2",
			'filter' => 'filter thing',
		);

		$this->assertEquals( $saved, $match->save( $source ) );
	}

	public function testBadData() {
		$match = new Custom_Match();
		$saved = array(
			'url_from' => '',
			'url_notfrom' => '',
			'filter' => '',
		);
		$this->assertEquals( $saved, $match->save( array( 'bad' => 'thing' ) ) );
	}

	public function testLoadBad() {
		$match = new Custom_Match();
		$match->load( serialize( array( 'url_from' => 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', 'url_notfrom' => 'yes', 'filter' => '', 'value' => '' ) ) );
		$this->assertEquals( 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}', $match->url_from );
	}

	public function testNoTargetNoUrl() {
		$action = new MockAction();
		add_filter( 'test_filter', array( $action, 'action' ), 10, 2 );

		$match = new Custom_Match( serialize( array( 'filter' => 'test_filter', 'url_from' => '', 'url_notfrom' => '' ) ) );

		// filter fires, but nothing is connected so it returns false
		$this->assertEquals( false, $match->get_target( 'a', 'b', new Red_Source_Flags() ) );
		$this->assertEquals( 1, $action->get_call_count() );
	}

	public function testNoTargetNotFrom() {
		$action = function() {
			return false;
		};
		add_filter( 'test_filter', $action, 10, 2 );

		$match = new Custom_Match( serialize( array( 'filter' => 'test_filter', 'url_from' => '/from', 'url_notfrom' => '/notfrom' ) ) );
		$this->assertEquals( '/notfrom', $match->get_target( 'a', 'b', new Red_Source_Flags() ) );
		remove_filter( 'test_filter', $action, 10, 2 );
	}

	public function testNoTargetFrom() {
		$action = function() {
			return true;
		};
		add_filter( 'test_filter', $action, 10, 2 );

		$match = new Custom_Match( serialize( array( 'filter' => 'test_filter', 'url_from' => '/from', 'url_notfrom' => '/notfrom' ) ) );
		$this->assertEquals( '/from', $match->get_target( 'a', 'b', new Red_Source_Flags() ) );
		remove_filter( 'test_filter', $action, 10, 2 );
	}

	public function testNoTargetFromRegex() {
		$action = function() {
			return true;
		};
		add_filter( 'test_filter', $action, 10, 2 );

		$match = new Custom_Match( serialize( array( 'filter' => 'test_filter', 'url_from' => '/from$1', 'url_notfrom' => '/notfrom' ) ) );
		$this->assertEquals( '/from1', $match->get_target( '/from1', '/from(.*)', new Red_Source_Flags( [ 'regex' => true ] ) ) );
		remove_filter( 'test_filter', $action, 10, 2 );
	}
}
