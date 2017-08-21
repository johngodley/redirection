<?php

require dirname( __FILE__ ) . '/../../fileio/apache.php';

class ApacheTest extends WP_UnitTestCase {
	public function testEmpty() {
		$apache = new Red_Apache_File();
		$items = $apache->load( 0, '', '' );

		$this->assertEquals( 0, $items );
	}

	public function testRewriteRule() {
		$apache = new Red_Apache_File();
		$item = $apache->get_as_item( 'RewriteRule /thing /newthing [301,L]' );

		$this->assertEquals( '/thing', $item['source'] );
		$this->assertEquals( '/newthing', $item['target'] );
		$this->assertEquals( '301', $item['code'] );
	}

	public function testRedirect() {
		$apache = new Red_Apache_File();
		$item = $apache->get_as_item( 'Redirect permanent /thing /newthing' );

		$this->assertEquals( '/thing', $item['source'] );
		$this->assertEquals( '/newthing', $item['target'] );
		$this->assertEquals( '301', $item['code'] );
	}

	public function testRedirectQuoted() {
		$apache = new Red_Apache_File();
		$item = $apache->get_as_item( 'Redirect permanent "/products/space thing/again thing.html" /products/again-thing/' );

		$this->assertEquals( '/products/space thing/again thing.html', $item['source'] );
		$this->assertEquals( '/products/again-thing/', $item['target'] );
		$this->assertEquals( '301', $item['code'] );
	}

	public function testRedirect302() {
		$apache = new Red_Apache_File();
		$item = $apache->get_as_item( 'Redirect /thing /newthing' );

		$this->assertEquals( '/thing', $item['source'] );
		$this->assertEquals( '/newthing', $item['target'] );
		$this->assertEquals( '302', $item['code'] );
	}

	public function testRedirectMatch() {
		$apache = new Red_Apache_File();
		$item = $apache->get_as_item( 'RedirectMatch permanent /thing /newthing' );

		$this->assertEquals( '/thing', $item['source'] );
		$this->assertEquals( '/newthing', $item['target'] );
		$this->assertEquals( '301', $item['code'] );
		$this->assertTrue( $item['regex'] );
	}

	public function testRedirectMatch302() {
		$apache = new Red_Apache_File();
		$item = $apache->get_as_item( 'RedirectMatch /thing /newthing' );

		$this->assertEquals( '/thing', $item['source'] );
		$this->assertEquals( '/newthing', $item['target'] );
		$this->assertEquals( '302', $item['code'] );
		$this->assertTrue( $item['regex'] );
	}

	public function testRedirectWithHash() {
		$apache = new Red_Apache_File();
		$item = $apache->get_as_item( 'Redirect permanent /thing/other /other/thing#1' );

		$this->assertEquals( '/thing/other', $item['source'] );
		$this->assertEquals( '/other/thing#1', $item['target'] );
		$this->assertEquals( '301', $item['code'] );
	}

	public function testRewriteNoCode() {
		$apache = new Red_Apache_File();
		$item = $apache->get_as_item( 'RewriteRule ^products/reporting /products/ [R,L]' );

		$this->assertEquals( '/products/reporting', $item['source'] );
		$this->assertEquals( '/products/', $item['target'] );
		$this->assertEquals( '302', $item['code'] );
	}
}
