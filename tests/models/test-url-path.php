<?php

class UrlPathTest extends WP_UnitTestCase {
	public function testPathMatch() {
		$url = new Red_Url_Path( '/test' );
		$this->assertTrue( $url->is_match( '/test', new Red_Source_Flags() ) );
	}

	public function testPathNotMatch() {
		$url = new Red_Url_Path( '/test1' );
		$this->assertFalse( $url->is_match( '/test2', new Red_Source_Flags() ) );
		$this->assertFalse( $url->is_match( '/tEst1', new Red_Source_Flags() ) );
		$this->assertFalse( $url->is_match( '/test1/', new Red_Source_Flags() ) );
	}

	public function testPathMatchCase() {
		$url = new Red_Url_Path( '/test1' );
		$this->assertTrue( $url->is_match( '/teSt1', new Red_Source_Flags( [ 'case' => true ] ) ) );
	}

	public function testPathMatchTrailing() {
		$url = new Red_Url_Path( '/test1/' );
		$this->assertTrue( $url->is_match( '/test1', new Red_Source_Flags( [ 'trailing' => true ] ) ) );
	}
}
