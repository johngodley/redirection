<?php

use Redirection\ImportExport\Format\Nginx;

class NginxTest extends WP_UnitTestCase {
	public function setUp(): void {
		parent::setUp();

		// Ensure options database value exists with defaults
		delete_option( Red_Options::OPTION_KEY );
		Red_Options::save( [ 'flag_case' => false, 'flag_trailing' => false ] );
	}

	public function testEmpty() {
		$nginx = new Nginx();
		$file = $nginx->get_data( [], [] );

		$lines = explode( "\n", $file );

		$this->assertEquals( 'server {', trim( $lines[4] ) );
		$this->assertEquals( '}', trim( $lines[5] ) );
	}

	public function testNew() {
		$nginx = new Nginx();
		$redirects = [ new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'action_type' => 'url', 'status' => 'enabled' ] ) ];

		$file = $nginx->get_data( $redirects, [] );
		$lines = explode( "\n", $file );

		$this->assertEquals( count( $lines ), 10 );
		$this->assertEquals( '# Created by Redirection', trim( $lines[0] ) );
		$this->assertEquals( 'server {', trim( $lines[4] ) );
		$this->assertEquals( 'rewrite ^$  redirect;', trim( $lines[5] ) );
		$this->assertEquals( '}', trim( $lines[ count( $lines ) - 4 ] ) );
		$this->assertEquals( '# End of Redirection', trim( $lines[ count( $lines ) - 2 ] ) );
	}

	public function testInvalidRegex() {
		$regex = "something\nwith newline";
		$nginx = new Nginx();
		$redirects = [ new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => $regex, 'action_data' => $regex, 'action_code' => 301 ] ) ];

		$file = $nginx->get_data( $redirects, [] );
		$lines = explode( "\n", $file );

		$this->assertEquals( count( $lines ), 10 );
		$this->assertEquals( 'rewrite ^something$ something permanent;', trim( $lines[5] ) );
	}

	public function testRegexStartEnd() {
		$nginx = new Nginx();
		$redirects = [ new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'action_type' => 'url', 'url' => '^/test$', 'action_data' => '/target', 'action_code' => 301 ] ) ];

		$file = $nginx->get_data( $redirects, [] );
		$lines = explode( "\n", $file );

		$this->assertEquals( 'rewrite ^/test$ /target permanent;', trim( $lines[5] ) );
	}

	public function testCaseInsensitive() {
		$match_data = json_encode( [ 'source' => [ 'flag_case' => true ] ] );
		$nginx = new Nginx();
		$redirects = [ new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'action_type' => 'url', 'url' => '/test', 'action_data' => '/target', 'action_code' => 301, 'match_data' => $match_data ] ) ];

		$file = $nginx->get_data( $redirects, [] );
		$lines = explode( "\n", $file );

		$this->assertEquals( 'rewrite (?i)^/test$ /target permanent;', trim( $lines[5] ) );
	}

	public function testErrorCodeUsesExactLocation() {
		$nginx = new Nginx();
		$redirects = [ new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'action_type' => 'error', 'url' => '/gone', 'action_code' => 410 ] ) ];

		$file = $nginx->get_data( $redirects, [] );
		$lines = explode( "\n", $file );

		$this->assertEquals( 'location = /gone {', trim( $lines[5] ) );
		$this->assertEquals( 'return 410;', trim( $lines[6] ) );
		$this->assertEquals( '}', trim( $lines[7] ) );
	}

	public function testErrorCodeUsesRegexLocationWhenCaseInsensitive() {
		$match_data = json_encode( [ 'source' => [ 'flag_case' => true ] ] );
		$nginx = new Nginx();
		$redirects = [ new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'action_type' => 'error', 'url' => '/test', 'action_code' => 451, 'match_data' => $match_data ] ) ];

		$file = $nginx->get_data( $redirects, [] );
		$lines = explode( "\n", $file );

		$this->assertEquals( 'location ~* ^/test$ {', trim( $lines[5] ) );
		$this->assertEquals( 'return 451;', trim( $lines[6] ) );
		$this->assertEquals( '}', trim( $lines[7] ) );
	}

	public function testErrorCodeUsesRegexLocationWhenRegex() {
		$nginx = new Nginx();
		$redirects = [ new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'error', 'url' => '^/test.*$', 'action_code' => 410 ] ) ];

		$file = $nginx->get_data( $redirects, [] );
		$lines = explode( "\n", $file );

		$this->assertEquals( 'location ~ ^/test.*$ {', trim( $lines[5] ) );
		$this->assertEquals( 'return 410;', trim( $lines[6] ) );
		$this->assertEquals( '}', trim( $lines[7] ) );
	}
}
