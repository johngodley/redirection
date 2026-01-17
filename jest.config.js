const defaultConfig = require( '@wordpress/scripts/config/jest-unit.config.js' );

module.exports = {
	...defaultConfig,
	moduleNameMapper: {
		...defaultConfig.moduleNameMapper,
		'^@wp-plugin-lib$': '<rootDir>/src/wp-plugin-lib',
		'^@wp-plugin-components$': '<rootDir>/src/wp-plugin-components',
	},
};
