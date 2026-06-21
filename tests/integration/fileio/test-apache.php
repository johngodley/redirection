<?php

use Redirection\FileIO\Format\Apache;

class ApacheTest extends WP_UnitTestCase {
	public function testEmpty() {
		$apache = new Apache();
		$items = $apache->load( 0, '', '' );

		$this->assertEquals( 0, $items );
	}

	public function testRewriteRule() {
		$apache = new Apache();
		$item = $apache->get_as_item( 'RewriteRule /thing /newthing [301,L]' );

		$this->assertEquals( '/thing', $item['url'] );
		$this->assertEquals( [ 'url' => '/newthing' ], $item['action_data'] );
		$this->assertEquals( '301', $item['action_code'] );
	}

	public function testRedirect() {
		$apache = new Apache();
		$item = $apache->get_as_item( 'Redirect permanent /thing /newthing' );

		$this->assertEquals( '/thing', $item['url'] );
		$this->assertEquals( [ 'url' => '/newthing' ], $item['action_data'] );
		$this->assertEquals( '301', $item['action_code'] );
	}

	public function testRedirectQuoted() {
		$apache = new Apache();
		$item = $apache->get_as_item( 'Redirect permanent "/products/space thing/again thing.html" /products/again-thing/' );

		$this->assertEquals( '/products/space thing/again thing.html', $item['url'] );
		$this->assertEquals( [ 'url' => '/products/again-thing/' ], $item['action_data'] );
		$this->assertEquals( '301', $item['action_code'] );
	}

	public function testRedirect302() {
		$apache = new Apache();
		$item = $apache->get_as_item( 'Redirect /thing /newthing' );

		$this->assertEquals( '/thing', $item['url'] );
		$this->assertEquals( [ 'url' => '/newthing' ], $item['action_data'] );
		$this->assertEquals( '302', $item['action_code'] );
	}

	public function testRedirectMatch() {
		$apache = new Apache();
		$item = $apache->get_as_item( 'RedirectMatch permanent /thing /newthing' );

		$this->assertEquals( '/thing', $item['url'] );
		$this->assertEquals( [ 'url' => '/newthing' ], $item['action_data'] );
		$this->assertEquals( '301', $item['action_code'] );
		$this->assertTrue( $item['regex'] );
	}

	public function testRedirectMatch302() {
		$apache = new Apache();
		$item = $apache->get_as_item( 'RedirectMatch /thing /newthing' );

		$this->assertEquals( '/thing', $item['url'] );
		$this->assertEquals( [ 'url' => '/newthing' ], $item['action_data'] );
		$this->assertEquals( '302', $item['action_code'] );
		$this->assertTrue( $item['regex'] );
	}

	public function testRedirectWithHash() {
		$apache = new Apache();
		$item = $apache->get_as_item( 'Redirect permanent /thing/other /other/thing#1' );

		$this->assertEquals( '/thing/other', $item['url'] );
		$this->assertEquals( [ 'url' => '/other/thing#1' ], $item['action_data'] );
		$this->assertEquals( '301', $item['action_code'] );
	}

	public function testRewriteNoCode() {
		$apache = new Apache();
		$item = $apache->get_as_item( 'RewriteRule ^products/reporting$ /products/ [R,L]' );

		$this->assertEquals( '/products/reporting', $item['url'] );
		$this->assertEquals( [ 'url' => '/products/' ], $item['action_data'] );
		$this->assertEquals( '302', $item['action_code'] );
	}

	public function testRedirectWithExtension() {
		$apache = new Apache();
		$item = $apache->get_as_item( 'RewriteRule ^products/reporting\.html.*$ /products/ [R,L]' );

		$this->assertEquals( '^/products/reporting\.html.*$', $item['url'] );
	}

	public function testRewriteRulePreservesOptionalRegexEnd() {
		$apache = new Apache();
		$item = $apache->get_as_item( 'RewriteRule ^/contact-us(/.*)?$ /contact/ [R=301,L]' );

		$this->assertEquals( '^/contact-us(/.*)?$', $item['url'] );
		$this->assertTrue( $item['regex'] );
		$this->assertEquals( [ 'url' => '/contact/' ], $item['action_data'] );
		$this->assertEquals( '301', $item['action_code'] );
	}
}
