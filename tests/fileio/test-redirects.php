<?php

require_once dirname( __FILE__ ) . '/../../fileio/redirects.php';

class ExportRedirectsTest extends WP_UnitTestCase {
	public function testNoRegex() {
		$exporter = new Red_Redirects_File();
		$item = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source', 'action_data' => '/target', 'action_code' => 301 ) );
		$lines = $exporter->item_as_lines( $item );

		$this->assertEquals( array(
			array( '/source', '', '/target', '301!', '', '' ),
		), $lines );
	}

	public function testRegex() {
		$exporter = new Red_Redirects_File();
		$item = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => '/source/(.*)/(.*)/(.*)', 'action_data' => '/target/$2/$1/$1/$3', 'action_code' => 302 ) );
		$lines = $exporter->item_as_lines( $item );

		$this->assertEquals( array(
			array( '/source/:path1/:path2/:splat', '', '/target/:path2/:path1/:path1/:splat', '302!', '', '' ),
		), $lines );
	}
	public function testTitle() {
		$exporter = new Red_Redirects_File();
		$item = new Red_Item( (object) array( 'title' => 'the title', 'match_type' => 'url', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source', 'action_data' => '/target', 'action_code' => 301 ) );
		$lines = $exporter->item_as_lines( $item );

		$this->assertEquals( array(
			array( '/source', '', '/target', '301!', '', '# the title' ),
		), $lines );
	}

	public function testEscapeFormula() {
		$exporter = new Red_Redirects_File();
		$item = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source/.*', 'action_data' => '/target/$1/${2}', 'action_code' => 301 ) );
		$lines = $exporter->item_as_lines( $item );

		$this->assertEquals( array(
			array( '/source/.*', '', '/target/$1/${2}', '301!', '', '' ),
		), $lines );
	}

	public function testLanguage() {
		$exporter = new Red_Redirects_File();
		$action_data = serialize( array(
			'language'    => 'fr-fr,en-us',
			'url_from'    => '/target1',
			'url_notfrom' => '/target2',
		) );
		$item = new Red_Item( (object) array( 'action_data' => $action_data, 'match_type' => 'language', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source', 'action_code' => 301 ) );
		$lines = $exporter->item_as_lines( $item );

		$this->assertEquals( array(
			array( '/source', '', '/target1', '301!', 'Language=fr-fr,en-us', '' ),
			array( '/source', '', '/target2', '301!', '', '' ),
		), $lines );
	}

	public function testRole() {
		$exporter = new Red_Redirects_File();
		$action_data = serialize( array(
			'role'        => 'the-role',
			'url_from'    => '/target1',
			'url_notfrom' => '/target2',
		) );
		$item = new Red_Item( (object) array( 'action_data' => $action_data, 'match_type' => 'role', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source', 'action_code' => 301 ) );
		$lines = $exporter->item_as_lines( $item );

		$this->assertEquals( array(
			array( '/source', '', '/target1', '301!', 'Role=the-role', '' ),
			array( '/source', '', '/target2', '301!', '', '' ),
		), $lines );
	}

	public function testCookie() {
		$exporter = new Red_Redirects_File();
		$action_data = serialize( array(
			'name'        => 'the-cookie',
			'value'       => 'the-value',
			'url_from'    => '/target1',
			'url_notfrom' => '/target2',
		) );
		$item = new Red_Item( (object) array( 'action_data' => $action_data, 'match_type' => 'cookie', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source', 'action_code' => 301 ) );
		$lines = $exporter->item_as_lines( $item );

		$this->assertEquals( array(
			array( '/source', '', '/target1', '301!', 'Cookie=the-cookie', '' ),
			array( '/source', '', '/target2', '301!', '', '' ),
		), $lines );
	}

	public function testNotSupported() {
		$exporter = new Red_Redirects_File();
		$item = new Red_Item( (object) array( 'match_type' => 'page', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source', 'action_code' => 301 ) );
		$lines = $exporter->item_as_lines( $item );

		$this->assertEquals( array(
			array( '# ERROR: Not supported item 1' ),
		), $lines );
	}
	
	public function testNotsupportedWithTitle() {
		$exporter = new Red_Redirects_File();
		$item = new Red_Item( (object) array( 'title' => 'the title', 'match_type' => 'page', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source', 'action_code' => 301 ) );
		$lines = $exporter->item_as_lines( $item );
		
		$this->assertEquals( array(
			array( '# ERROR: Not supported item 1 (the title)' ),
		), $lines );
	}

	public function testMultipleLines() {
		$exporter = new Red_Redirects_File();
		$item1 = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source1', 'action_data' => '/target', 'action_code' => 301 ) );
		$item2 = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source2', 'action_data' => '/target', 'action_code' => 301 ) );

		$result = $exporter->get_data( array( $item1, $item2 ), array() );

		$lines = explode( PHP_EOL, $result );

		$this->assertEquals( array(
			'# Group 0',
			'/source1 /target 301!',
			'/source2 /target 301!',
		), $lines );
	}

	public function testGrouped() {
		$exporter = new Red_Redirects_File();
		$group1 = Red_Group::create( 'My first group', 1 );
		$item1 = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source1', 'action_data' => '/target', 'action_code' => 301, 'group_id' => $group1->get_id() ) );
		$item2 = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source2', 'action_data' => '/target', 'action_code' => 301, 'group_id' => $group1->get_id() ) );

		$group2 = Red_Group::create( 'My second group', 2 );
		$item3 = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source3', 'action_data' => '/target', 'action_code' => 301, 'group_id' => $group2->get_id() ) );

		$result = $exporter->get_data( array( $item1, $item2, $item3 ), array( $group1->get_id() => $group1->to_json(), $group2->get_id() => $group2->to_json() ) );

		$lines = explode( PHP_EOL, $result );

		$this->assertEquals( array(
			"# Group {$group1->get_id()}: My first group",
			'/source1 /target 301!',
			'/source2 /target 301!',
			'',
			"# Group {$group2->get_id()}: My second group",
			'/source3 /target 301!',
		), $lines );
	}

	public function testDisabled() {
		$exporter = new Red_Redirects_File();
		$item = new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => false, 'action_type' => 'url', 'url' => '/source', 'action_data' => '/target', 'action_code' => 301 ) );
		$item->disable();
		$lines = $exporter->item_as_lines( $item );

		$this->assertCount( 0, $lines );
	}
}
