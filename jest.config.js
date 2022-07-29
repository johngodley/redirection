const baseConfig = require( '@wordpress/scripts/config/jest-unit.config.js' );

module.exports = {
	...baseConfig,
	moduleNameMapper: {
		...baseConfig.moduleNameMapper,
		"^@wp-plugin-components$": "<rootDir>/src/wp-plugin-components",
		"^@wp-plugin-lib$": "<rootDir>/src/wp-plugin-lib",
	},
	preset: '@wordpress/jest-preset-default',
};
