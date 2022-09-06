<?php

use Redirection\Plugin\Settings;

class SettingsTest extends WP_UnitTestCase {
	public function testGetDefaultOptions() {
		delete_option( REDIRECTION_OPTION );

		$defaults = Settings\red_get_default_options();
		$options = Settings\red_get_options();

		unset( $defaults['token'] );
		unset( $options['token'] );

		foreach ( $defaults as $name => $value ) {
			$this->assertTrue( isset( $options[ $name ] ) );
			$this->assertEquals( $value, $options[ $name ] );
		}
	}

	public function testGetDefaultOptionsAlreadyInstalled() {
		update_option( REDIRECTION_OPTION, [ 'token' => 'token' ] );

		$options = Settings\red_get_options();
		$this->assertFalse( $options['flag_case'] );
		$this->assertFalse( $options['flag_trailing'] );
		$this->assertFalse( $options['flag_regex'] );
		$this->assertEquals( 'exact', $options['flag_query'] );
	}

	public function testOptionOverride() {
		update_option( REDIRECTION_OPTION, array( 'token' => 'token' ) );

		$defaults = Settings\red_get_default_options();
		$options = Settings\red_get_options();

		foreach ( $defaults as $name => $value ) {
			if ( $name !== 'token' ) {
				$this->assertTrue( isset( $options[ $name ] ) );
				$this->assertEquals( $value, $options[ $name ] );
			}
		}

		$this->assertEquals( 'token', $options['token'] );
	}

	public function testRemoveOld() {
		update_option( REDIRECTION_OPTION, array( 'cat' => 'cat' ) );

		$options = Settings\red_get_options();
		$this->assertFalse( isset( $options['cat'] ) );
	}
}
