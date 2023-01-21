<?php

require dirname( __FILE__ ) . '/../../models/htaccess.php';

class HtaccessTest extends WP_UnitTestCase {
	private function getOutput( Red_Htaccess $htaccess ) {
		return explode( "\n", $htaccess->get() );
	}

	private function getExisting() {
		return 'this is a line

# Created by Redirection
# End of Redirection

and a line at the end';
	}

	public function testEmpty() {
		$htaccess = new Red_Htaccess();
		$lines = $this->getOutput( $htaccess );

		$this->assertEquals( count( $lines ), 1 );
	}

	public function testNew() {
		$htaccess = new Red_Htaccess();
		$htaccess->add( new Red_Item( (object)array( 'match_type' => 'url', 'id' => 1, 'action_type' => 'url' ) ) );

		$lines = $this->getOutput( $htaccess );

		$this->assertEquals( count( $lines ), 9 );
		$this->assertEquals( '# Created by Redirection', trim( $lines[0] ) );
		$this->assertEquals( '<IfModule mod_rewrite.c>', trim( $lines[4] ) );
		$this->assertEquals( '</IfModule>', trim( $lines[6] ) );
		$this->assertEquals( '# End of Redirection', trim( $lines[count( $lines ) - 1] ) );
	}

	public function testReplaceExisting() {
		$htaccess = new Red_Htaccess();
		$htaccess->add( new Red_Item( (object)array( 'match_type' => 'url', 'id' => 1, 'action_type' => 'url' ) ) );
		$file = $htaccess->get( $this->getExisting() );
		$lines = explode( "\n", $file );

		$this->assertEquals( 'this is a line', trim( $lines[0] ) );
		$this->assertEquals( '# Created by Redirection', trim( $lines[2] ) );
		$this->assertEquals( '', trim( $lines[count( $lines ) - 2] ) );
		$this->assertEquals( 'and a line at the end', trim( $lines[count( $lines ) - 1] ) );
	}

	public function testAddToStart() {
		$htaccess = new Red_Htaccess();
		$htaccess->add( new Red_Item( (object)array( 'match_type' => 'url', 'id' => 1, 'action_type' => 'url' ) ) );
		$file = $htaccess->get( "this is\nan existing file\n" );
		$lines = explode( "\n", $file );

		$this->assertEquals( '# Created by Redirection', trim( $lines[0] ) );
		$this->assertEquals( '# End of Redirection', trim( $lines[ count( $lines ) - 4 ] ) );
		$this->assertEquals( 'this is', trim( $lines[ count( $lines ) - 2 ] ) );
		$this->assertEquals( 'an existing file', trim( $lines[ count( $lines ) - 1 ] ) );
	}

	public function testRemoveExisting() {
		$htaccess = new Red_Htaccess();
		$file = $htaccess->get( $this->getExisting() );
		$lines = explode( "\n", $file );

		$this->assertEquals( 'this is a line', $lines[ 0 ] );
		$this->assertEquals( 'and a line at the end', $lines[ count( $lines ) - 1 ] );
	}

	public function testRedirectUrl() {
		$htaccess = new Red_Htaccess();
		$htaccess->add( new Red_Item( (object)array( 'match_type' => 'url', 'id' => 1, 'action_type' => 'url', 'url' => '/my-test', 'action_code' => 301 ) ) );
		$htaccess->add( new Red_Item( (object)array( 'match_type' => 'url', 'id' => 1, 'action_type' => 'url', 'url' => '/my-test.php', 'action_code' => 302 ) ) );

		$lines = $this->getOutput( $htaccess );

		$this->assertEquals( 'RewriteRule ^my-test$ [R=301,L]', trim( $lines[5] ) );
		$this->assertEquals( 'RewriteRule ^my-test\.php$ [R=302,L]', trim( $lines[6] ) );
	}

	public function testRedirectUrlHash() {
		$htaccess = new Red_Htaccess();
		$htaccess->add( new Red_Item( (object)array( 'match_type' => 'url', 'id' => 1, 'action_type' => 'url', 'url' => '/my-test', 'action_code' => 301, 'action_data' => '/target#hash' ) ) );

		$lines = $this->getOutput( $htaccess );

		$this->assertEquals( 'RewriteRule ^my-test$ /target#hash [R=301,L,NE]', trim( $lines[5] ) );
	}

	public function testRedirectUrlRegex() {
		$htaccess = new Red_Htaccess();
		$htaccess->add( new Red_Item( (object)array( 'match_type' => 'url', 'id' => 1, 'action_type' => 'url', 'url' => '/my\.test.*?', 'action_code' => 301, 'regex' => true ) ) );

		$lines = $this->getOutput( $htaccess );

		$this->assertEquals( 'RewriteRule my\.test.*? [R=301,L]', trim( $lines[5] ) );
	}

	public function testRedirectUrlRegexLimit() {
		$htaccess = new Red_Htaccess();
		$htaccess->add( new Red_Item( (object)array( 'match_type' => 'url', 'id' => 1, 'action_type' => 'url', 'url' => '^/my-test.*?$', 'action_code' => 301, 'regex' => true ) ) );

		$lines = $this->getOutput( $htaccess );

		$this->assertEquals( 'RewriteRule ^my-test.*?$ [R=301,L]', trim( $lines[5] ) );
	}

	public function testError() {
		$htaccess = new Red_Htaccess();
		$htaccess->add( new Red_Item( (object)array( 'match_type' => 'url', 'id' => 1, 'action_type' => 'error', 'url' => '/my-test', 'action_code' => 404 ) ) );
		$htaccess->add( new Red_Item( (object)array( 'match_type' => 'url', 'id' => 1, 'action_type' => 'error', 'url' => '/my-test.php', 'action_code' => 410 ) ) );

		$lines = $this->getOutput( $htaccess );

		$this->assertEquals( 'RewriteRule ^my-test$ / [F]', trim( $lines[5] ) );
		$this->assertEquals( 'RewriteRule ^my-test\.php$ / [G]', trim( $lines[6] ) );
	}

	public function testRedirectUrlWithQuery() {
		$htaccess = new Red_Htaccess();
		$htaccess->add( new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'action_type' => 'url', 'url' => '/my-test?query=1', 'action_code' => 301 ) ) );
		$htaccess->add( new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'action_type' => 'url', 'url' => '/my-test.php?query=1&thing=2', 'action_code' => 302 ) ) );

		$lines = $this->getOutput( $htaccess );

		$this->assertEquals( 'RewriteCond %{QUERY_STRING} ^query=1$', trim( $lines[5] ) );
		$this->assertEquals( 'RewriteRule ^my-test$ [R=301,L]', trim( $lines[6] ) );
		$this->assertEquals( 'RewriteCond %{QUERY_STRING} ^query=1&thing=2$', trim( $lines[7] ) );
		$this->assertEquals( 'RewriteRule ^my-test\.php$ [R=302,L]', trim( $lines[8] ) );
	}

	public function testRedirectUrlWithTargetQuery() {
		$htaccess = new Red_Htaccess();
		$htaccess->add( new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'action_type' => 'url', 'url' => '/my-test', 'action_data' => '/target?test=1&test=2%20', 'action_code' => 301 ) ) );

		$lines = $this->getOutput( $htaccess );

		$this->assertEquals( 'RewriteRule ^my-test$ /target?test=1&test=2%20 [R=301,L,NE]', trim( $lines[5] ) );
	}

	public function testInvalidRegex() {
		$regex = "something\nwith newline";
		$htaccess = new Red_Htaccess();
		$htaccess->add( new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => $regex, 'action_data' => $regex, 'action_code' => 301 ) ) );

		$lines = $this->getOutput( $htaccess );

		$this->assertEquals( count( $lines ), 9 );
		$this->assertEquals( 'RewriteRule something something%0Awith%20newline [R=301,L]', trim( $lines[5] ) );
	}

	public function testRegexInData() {
		$regex = "/$1";
		$htaccess = new Red_Htaccess();
		$htaccess->add( new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => '/blog/(.*)', 'action_data' => $regex, 'action_code' => 301 ) ) );

		$file = $htaccess->get( $this->getExisting() );
		$lines = explode( "\n", $file );

		$this->assertEquals( count( $lines ), 13 );
		$this->assertEquals( 'RewriteRule blog/(.*) /$1 [R=301,L]', trim( $lines[7] ) );
	}

	public function testCaseInsensitive() {
		$match_data = json_encode( [ 'source' => [ 'flag_case' => true ] ] );
		$htaccess = new Red_Htaccess();
		$htaccess->add( new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'action_type' => 'url', 'url' => '/test', 'action_data' => '/target', 'action_code' => 301, 'match_data' => $match_data ] ) );

		$lines = $this->getOutput( $htaccess );
		$this->assertEquals( 'RewriteRule ^test$ /target [R=301,L,NC]', trim( $lines[5] ) );
	}

	public function testPassQuery() {
		$match_data = json_encode( [ 'source' => [ 'flag_query' => 'pass' ] ] );
		$htaccess = new Red_Htaccess();
		$htaccess->add( new Red_Item( (object) [ 'match_type' => 'url', 'id' => 1, 'action_type' => 'url', 'url' => '/test', 'action_data' => '/target', 'action_code' => 301, 'match_data' => $match_data ] ) );

		$lines = $this->getOutput( $htaccess );
		$this->assertEquals( 'RewriteRule ^test$ /target [R=301,L,QSA]', trim( $lines[5] ) );
	}

	public function testServerRedirect() {
		$action_data = serialize( [ 'server' => 'https://otherdomain.com', 'url_notfrom' => '/target', 'url_from' => '/target' ] );
		$item = new Red_Item( (object) [ 'match_type' => 'server', 'id' => 1, 'action_type' => 'url', 'url' => '/test', 'action_data' => $action_data, 'action_code' => 301 ] );

		$htaccess = new Red_Htaccess();
		$htaccess->add( $item );

		$lines = $this->getOutput( $htaccess );

		$this->assertEquals( 'RewriteCond %{HTTP_HOST} ^otherdomain\.com$ [NC]', trim( $lines[5] ) );
		$this->assertEquals( 'RewriteRule ^test$ /target [R=301,L]', trim( $lines[6] ) );
	}
}
