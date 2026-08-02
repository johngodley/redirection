<?php

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

define( 'PLUGIN_PATH', dirname( __DIR__ ) );

$redirection_refactor = filter_var( getenv( 'REDIRECTION_REFACTOR' ), FILTER_VALIDATE_BOOLEAN );

if ( $redirection_refactor && ! defined( 'REDIRECTION_REFACTOR' ) ) {
	define( 'REDIRECTION_REFACTOR', true );
}

if ( defined( 'REDIRECTION_REFACTOR' ) && REDIRECTION_REFACTOR ) {
	require_once PLUGIN_PATH . '/redirection-refactor.php';
}

require PLUGIN_PATH . '/tests/unit-test.php';
