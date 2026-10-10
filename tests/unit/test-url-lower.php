<?php

require_once PLUGIN_PATH . '/models/url/url-path.php';
require_once PLUGIN_PATH . '/models/url/url-lower.php';

use Brain\Monkey\Functions;

class UrlLowerTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		Functions\expect( 'apply_filters' )
			->andReturnUsing(
				function ( $tag, $value ) {
					return $value;
				}
			);
	}

	public function testNoChange() {
		$this->assertFalse( Red_Url_Lowercase::get_target( '/already-lower', 'https://example.org' ) );
	}

	public function testUppercasePath() {
		$this->assertEquals( 'https://example.org/mypage', Red_Url_Lowercase::get_target( '/MyPage', 'https://example.org' ) );
	}

	public function testPreservesQuery() {
		$this->assertEquals( 'https://example.org/mypage?x=1', Red_Url_Lowercase::get_target( '/MyPage?x=1', 'https://example.org' ) );
	}

	public function testProtectedPath() {
		$this->assertFalse( Red_Url_Lowercase::get_target( '/wp-admin/Settings', 'https://example.org' ) );
	}

	public function testProtectedLogin() {
		$this->assertFalse( Red_Url_Lowercase::get_target( '/wp-login.php', 'https://example.org' ) );
	}

	public function testProtectedRest() {
		$this->assertFalse( Red_Url_Lowercase::get_target( '/wp-json/wp/v2/posts', 'https://example.org' ) );
	}

	public function testCanonicalTargetBase() {
		$this->assertEquals( 'https://www.example.org/mypage', Red_Url_Lowercase::get_target( '/MyPage', 'https://www.example.org/MyPage' ) );
	}

	public function testSubdirectory() {
		$this->assertEquals( 'https://example.org/wp/mypage', Red_Url_Lowercase::get_target( '/wp/MyPage', 'https://example.org/wp' ) );
	}

	public function testUtf8Path() {
		$this->assertEquals( 'https://example.org/café', Red_Url_Lowercase::get_target( '/Café', 'https://example.org' ) );
	}
}
