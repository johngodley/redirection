<?php

class UrlTransformTest extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();

		$this->admin_user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
	}

	public function testNoTransform() {
		$before = 'hello world';
		$after = $before;
		$transform = new Red_Url_Transform();

		$this->assertEquals( $after, $transform->transform( $before ) );
	}

	public function testNumbericPost() {
		$before = 'hello world';
		$after = $before;
		$transform = new Red_Url_Transform();

		$this->assertEquals( $after, $transform->transform( $before ) );
	}

	public function testUserId() {
		wp_set_current_user( $this->admin_user_id );

		$before = 'hello [userid]';
		$after = 'hello 1';
		$transform = new Red_Url_Transform();

		$this->assertEquals( $after, $transform->transform( $before ) );
	}

	public function testUserLogin() {
		wp_set_current_user( $this->admin_user_id );

		$before = 'hello [userlogin]';
		$after = 'hello user 0';
		$transform = new Red_Url_Transform();

		$this->assertEquals( $after, strtolower( substr( $transform->transform( $before ), 0, strlen( $after ) ) ) );
	}

	public function testUnixTime() {
		$before = 'hello [unixtime]';
		$transform = new Red_Url_Transform();

		$this->assertEquals( 1, preg_match( '/hello \d*/', $transform->transform( $before ), $matches ) );
	}

	public function testMD5() {
		$before = 'hello [md5]world[/md5]';
		$after = 'hello 7d793037a0760186574b0282f2f435e7';
		$transform = new Red_Url_Transform();

		$this->assertEquals( $after, $transform->transform( $before ) );
	}

	public function testUpper() {
		$before = '[upper]hello world[/upper]';
		$after = 'HELLO WORLD';
		$transform = new Red_Url_Transform();

		$this->assertEquals( $after, $transform->transform( $before ) );
	}

	public function testLower() {
		$before = '[lower]HELLO WORLD[/lower]';
		$after = 'hello world';
		$transform = new Red_Url_Transform();

		$this->assertEquals( $after, $transform->transform( $before ) );
	}

	public function testDashes() {
		$before = '[dashes]hello world_here[/dashes]';
		$after = 'hello-world-here';
		$transform = new Red_Url_Transform();

		$this->assertEquals( $after, $transform->transform( $before ) );
	}

	public function testUnderscores() {
		$before = '[underscores]hello world-here[/underscores]';
		$after = 'hello_world_here';
		$transform = new Red_Url_Transform();

		$this->assertEquals( $after, $transform->transform( $before ) );
	}
}
