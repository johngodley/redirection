<?php

require dirname( __FILE__ ) . '/../models/htaccess.php';

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

	// XXX test each redirection output
}

