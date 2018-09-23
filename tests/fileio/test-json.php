<?php

require dirname( __FILE__ ) . '/../../fileio/json.php';

class JsonTest extends WP_UnitTestCase {
	public function testExportEmpty() {
		$json = new Red_Json_File();
		$data = json_decode( $json->get_data( array(), array() ) );

		$this->assertTrue( empty( $data->groups ) );
		$this->assertTrue( empty( $data->redirects ) );
		$this->assertTrue( isset( $data->plugin->version ) );
	}

	public function testExportNew() {
		$json = new Red_Json_File();
		$redirects = array( new Red_Item( (object)array( 'url' => 'source', 'match_type' => 'url', 'id' => 1, 'action_type' => 'url' ) ) );
		$groups = array( new Red_Group( (object)array( 'name' => 'group', 'id' => 1 ) ) );

		$data = json_decode( $json->get_data( $redirects, $groups ) );
		$this->assertEquals( 'source', $data->redirects[ 0 ]->url );
		$this->assertEquals( 1, $data->groups[ 0 ]->id );
	}

	public function testImportBad() {
		$json = new Red_Json_File();
		$data = $json->load( 0, 'thing', 'x' );
		$this->assertEquals( 0, $data );
	}

	public function testImport() {
		global $wpdb;

		$import = array(
			'groups' => array(
				array(
					'name' => 'groupx',
					'id' => 5,
					'module_id' => 1,
				)
			),
			'redirects' => array(
				array(
					'url' => 'source1',
					'id' => 1,
					'group_id' => 5,
					'match_type' => 'url',
					'action_type' => 'url',
					'action_data' => array( 'url' => '/test' ),
				)
			)
		);

		$json = new Red_Json_File();
		$data = $json->load( 0, 'thing', json_encode( $import ) );
		$this->assertEquals( 1, $data );

		$group = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_groups ORDER BY id DESC LIMIT 1" );
		$redirect = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY id DESC LIMIT 1" );

		$this->assertEquals( 'groupx', $group->name );
		$this->assertEquals( '/source1', $redirect->url );
		$this->assertEquals( $group->id, $redirect->group_id );
	}
}
