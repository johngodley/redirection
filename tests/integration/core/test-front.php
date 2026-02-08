<?php

class FrontTest extends WP_UnitTestCase {
	public function testNoFrontIfNotInstalled() {
		delete_option( Red_Options::OPTION_KEY );
		delete_option( 'redirection_version' );
		Red_Options::reset();

		$redirection = Redirection::init();
		$this->assertFalse( $redirection->can_start() );
	}

	public function testFrontIfInstalled() {
		update_option( Red_Options::OPTION_KEY, array( 'database' => REDIRECTION_DB_VERSION ) );
		Red_Options::reset();

		$redirection = Redirection::init();
		$this->assertTrue( $redirection->can_start() );
	}

	public function testFrontIfNeedUpgrade() {
		update_option( Red_Options::OPTION_KEY, array( 'database' => '1.5' ) );
		Red_Options::reset();

		$redirection = Redirection::init();
		$this->assertTrue( $redirection->can_start() );
	}

	public function testFrontIfOldInstall() {
		delete_option( Red_Options::OPTION_KEY );
		update_option( 'redirection_version', '1.2' );
		Red_Options::reset();

		$redirection = Redirection::init();
		$this->assertTrue( $redirection->can_start() );
	}

	public function testMaskIp4() {
		$redirection = Redirection::init();

		$this->assertEquals( '192.168.1.0', $redirection->mask_ip( '192.168.1.1' ) );
	}

	public function testMaskIp6() {
		$redirection = Redirection::init();

		$this->assertEquals( '2001:db8:85a3:1::', $redirection->mask_ip( '2001:0db8:85a3:0001:0001:8a2e:0370:7334' ) );
	}

	public function testMaskEmptyIp() {
		$redirection = Redirection::init();

		$this->assertEquals( '', $redirection->mask_ip( '' ) );
	}

	public function testNoIp() {
		$redirection = Redirection::init();

		$this->assertEquals( '', $redirection->no_ip_logging( '192.168.1.1' ) );
	}
}
