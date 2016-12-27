<?php

require dirname( __FILE__ ) . '/../../models/htaccess.php';

class HtaccessTest extends WP_UnitTestCase {
	private function getExisting() {
		return 'this is a line

# Created by Redirection
# End of Redirection

and a line at the end';
	}

	public function testEmpty() {
		$htaccess = new Red_Htaccess();
		$file = $htaccess->get();

		$this->assertEmpty( $file );
	}

	public function testNew() {
		$htaccess = new Red_Htaccess();
		$htaccess->add( new Red_Item( (object)array( 'match_type' => 'url', 'id' => 1, 'action_type' => 'url' ) ) );

		$file = $htaccess->get();
		$lines = explode( "\n", $file );

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

	public function testAddToEnd() {
		$htaccess = new Red_Htaccess();
		$htaccess->add( new Red_Item( (object)array( 'match_type' => 'url', 'id' => 1, 'action_type' => 'url' ) ) );
		$file = $htaccess->get( "this is\nan existing file\n" );
		$lines = explode( "\n", $file );

		$this->assertEquals( 'this is', trim( $lines[0] ) );
		$this->assertEquals( 'an existing file', trim( $lines[1] ) );
		$this->assertEquals( '', trim( $lines[2] ) );
		$this->assertEquals( '# Created by Redirection', trim( $lines[3] ) );
		$this->assertEquals( '# End of Redirection', trim( $lines[count( $lines ) - 1] ) );
	}

	public function testRemoveExisting() {
		$existing_without_redirection = 'this is a line

and a line at the end';
		$htaccess = new Red_Htaccess();
		$file = $htaccess->get( $this->getExisting() );

		$this->assertEquals( $existing_without_redirection, $file );
	}

	public function testRedirectUrl() {
		$htaccess = new Red_Htaccess();
		$htaccess->add( new Red_Item( (object)array( 'match_type' => 'url', 'id' => 1, 'action_type' => 'url', 'url' => '/my-test', 'action_code' => 301 ) ) );
		$htaccess->add( new Red_Item( (object)array( 'match_type' => 'url', 'id' => 1, 'action_type' => 'url', 'url' => '/my-test.php', 'action_code' => 302 ) ) );
		$file = $htaccess->get();
		$lines = explode( "\n", $file );

		$this->assertEquals( 'RewriteRule ^/my-test$  [R=301,L]', trim( $lines[5] ) );
		$this->assertEquals( 'RewriteRule ^/my-test\.php$  [R=302,L]', trim( $lines[6] ) );
	}

	public function testError() {
		$htaccess = new Red_Htaccess();
		$htaccess->add( new Red_Item( (object)array( 'match_type' => 'url', 'id' => 1, 'action_type' => 'error', 'url' => '/my-test', 'action_code' => 404 ) ) );
		$htaccess->add( new Red_Item( (object)array( 'match_type' => 'url', 'id' => 1, 'action_type' => 'error', 'url' => '/my-test.php', 'action_code' => 410 ) ) );
		$file = $htaccess->get();
		$lines = explode( "\n", $file );

		$this->assertEquals( 'RewriteRule ^/my-test$ / [F]', trim( $lines[5] ) );
		$this->assertEquals( 'RewriteRule ^/my-test\.php$ / [G]', trim( $lines[6] ) );
	}

	public function testRedirectUrlWithQuery() {
		$htaccess = new Red_Htaccess();
		$htaccess->add( new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'action_type' => 'url', 'url' => '/my-test?query=1', 'action_code' => 301 ) ) );
		$htaccess->add( new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'action_type' => 'url', 'url' => '/my-test.php?query=1&thing=2', 'action_code' => 302 ) ) );
		$file = $htaccess->get();
		$lines = explode( "\n", $file );

		$this->assertEquals( 'RewriteCond %{QUERY_STRING} ^query=1$', trim( $lines[5] ) );
		$this->assertEquals( 'RewriteRule ^/my-test$  [R=301,L]', trim( $lines[6] ) );
		$this->assertEquals( 'RewriteCond %{QUERY_STRING} ^query=1&thing=2$', trim( $lines[7] ) );
		$this->assertEquals( 'RewriteRule ^/my-test\.php$  [R=302,L]', trim( $lines[8] ) );
	}

	public function testRedirectUrlWithTargetQuery() {
		$htaccess = new Red_Htaccess();
		$htaccess->add( new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'action_type' => 'url', 'url' => '/my-test', 'action_data' => '/target?test=1&test=2%20', 'action_code' => 301 ) ) );
		$file = $htaccess->get();
		$lines = explode( "\n", $file );

		$this->assertEquals( 'RewriteRule ^/my-test$ /target?test=1&test=2%20 [R=301,L]', trim( $lines[5] ) );
	}

	public function testInvalidRegex() {
		$regex = "something\nwith newline";
		$htaccess = new Red_Htaccess();
		$htaccess->add( new Red_Item( (object) array( 'match_type' => 'url', 'id' => 1, 'regex' => true, 'action_type' => 'url', 'url' => $regex, 'action_data' => $regex, 'action_code' => 301 ) ) );

		$file = $htaccess->get();
		$lines = explode( "\n", $file );

		$this->assertEquals( count( $lines ), 9 );
		$this->assertEquals( 'RewriteRule something something%0Awith%20newline [R=301,L]', trim( $lines[5] ) );
	}
}
