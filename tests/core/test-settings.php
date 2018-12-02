<?php

class SettingsTest extends WP_UnitTestCase {
	public function testGetDefaultOptions() {
		delete_option( REDIRECTION_OPTION );

		$defaults = red_get_default_options();
		$options = red_get_options();

		unset( $defaults['token'] );
		unset( $options['token'] );

		foreach ( $defaults as $name => $value ) {
			$this->assertTrue( isset( $options[ $name ] ) );
			$this->assertEquals( $value, $options[ $name ] );
		}
	}

	public function testOptionOverride() {
		update_option( REDIRECTION_OPTION, array( 'token' => 'token' ) );

		$defaults = red_get_default_options();
		$options = red_get_options();

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

		$options = red_get_options();
		$this->assertFalse( isset( $options['cat'] ) );
	}
}
