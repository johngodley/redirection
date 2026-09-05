<?php

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

define( 'PLUGIN_PATH', dirname( __DIR__ ) );

// Refactor mode is now the only supported mode, so it's always on for tests.
if ( ! defined( 'REDIRECTION_REFACTOR' ) ) {
	define( 'REDIRECTION_REFACTOR', true );
}

require_once PLUGIN_PATH . '/redirection-refactor.php';

require PLUGIN_PATH . '/tests/unit-test.php';
