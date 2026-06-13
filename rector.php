<?php

use Rector\Config\RectorConfig;

return RectorConfig::configure()
	->withPaths(
		[
			__DIR__ . '/actions',
			__DIR__ . '/api',
			__DIR__ . '/database',
			__DIR__ . '/fileio',
			__DIR__ . '/matches',
			__DIR__ . '/models',
			__DIR__ . '/modules',
			__DIR__ . '/redirection-admin.php',
			__DIR__ . '/redirection-capabilities.php',
			__DIR__ . '/redirection-cli.php',
			__DIR__ . '/redirection-front.php',
			__DIR__ . '/redirection-settings.php',
			__DIR__ . '/redirection.php',
		]
	)
	->withPhpSets();
