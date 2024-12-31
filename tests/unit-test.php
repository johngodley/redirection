<?php

use PHPUnit\Framework\TestCase as BaseTestCase;
use Brain\Monkey;

abstract class TestCase extends BaseTestCase {
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}
}

if ( ! function_exists( 'wp_parse_str' ) ) {
	function wp_parse_str( $input_string, &$result ) {
		parse_str( (string) $input_string, $result );

		return $result;
	}
}

if ( ! function_exists( 'wp_parse_url' ) ) {
	function wp_parse_url( $url, $component = -1 ) {
		return parse_url( $url, $component );
	}
}
