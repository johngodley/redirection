<?php

require dirname( __FILE__ ) . '/../fileio/nginx.php';

class NginxTest extends WP_UnitTestCase {
	public function testEmpty() {
		$nginx = new Red_Nginx_File();
		$file = $nginx->get( array() );

		$this->assertEmpty( $file );
	}

	public function testNew() {
		$nginx = new Red_Nginx_File();
		$redirects = array( new Red_Item( (object)array( 'match_type' => 'url', 'id' => 1, 'action_type' => 'url' ) ) );

		$file = $nginx->get( $redirects );
		$lines = explode( "\n", $file );

		$this->assertEquals( count( $lines ), 9 );
		$this->assertEquals( '# Created by Redirection', trim( $lines[0] ) );
		$this->assertEquals( 'server {', trim( $lines[4] ) );
		$this->assertEquals( 'rewrite ^$  redirect;', trim( $lines[5] ) );
		$this->assertEquals( '}', trim( $lines[count( $lines ) - 3] ) );
		$this->assertEquals( '# End of Redirection', trim( $lines[count( $lines ) - 1] ) );
	}
}
