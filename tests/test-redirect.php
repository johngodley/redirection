<?php

class RedirectTest extends WP_UnitTestCase {
	public function testRemoveHttp() {
		$this->assertEquals( Red_Item::sanitize_url( 'http://domain.com/some/url' ), '/some/url' );
		$this->assertEquals( Red_Item::sanitize_url( 'https://domain.com/some/url' ), '/some/url' );
	}

	public function testRemoveHash() {
		$this->assertEquals( Red_Item::sanitize_url( '/some/url#thing' ), '/some/url' );
	}

	public function testRemoveNewline() {
		$this->assertEquals( Red_Item::sanitize_url( "/some/url\nsomethingelse1" ), '/some/url' );
		$this->assertEquals( Red_Item::sanitize_url( "/some/url\rsomethingelse2" ), '/some/url' );
		$this->assertEquals( Red_Item::sanitize_url( "/some/url\r\nsomethingelse3" ), '/some/url' );
	}

	public function testAddLeadingSlash() {
		$this->assertEquals( Red_Item::sanitize_url( 'some/url' ), '/some/url' );
	}
}
