const defaultConfig = require( '@wordpress/scripts/config/jest-unit.config.js' );

module.exports = {
	...defaultConfig,
	moduleNameMapper: {
		...defaultConfig.moduleNameMapper,
		'^@wp-plugin-lib$': '<rootDir>/src/wp-plugin-lib',
		'^@wp-plugin-components$': '<rootDir>/src/wp-plugin-components',
		'^lib/(.*)$': '<rootDir>/src/lib/$1',
		'^component/(.*)$': '<rootDir>/src/component/$1',
		'^state/(.*)$': '<rootDir>/src/state/$1',
		'^page/(.*)$': '<rootDir>/src/page/$1',
		'^stores/(.*)$': '<rootDir>/src/stores/$1',
		'^types/(.*)$': '<rootDir>/src/types/$1',
	},
};
