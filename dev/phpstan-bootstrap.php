<?php

$version_file = dirname( __DIR__ ) . '/build/redirection-version.php';

if ( file_exists( $version_file ) ) {
	require_once $version_file;
	return;
}

if ( ! defined( 'REDIRECTION_VERSION' ) ) {
	define( 'REDIRECTION_VERSION', '5.8.0' );
}

if ( ! defined( 'REDIRECTION_BUILD' ) ) {
	define( 'REDIRECTION_BUILD', 'dev' );
}

if ( ! defined( 'REDIRECTION_MIN_WP' ) ) {
	define( 'REDIRECTION_MIN_WP', '6.6' );
}
