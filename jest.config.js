const defaultConfig = require( '@wordpress/scripts/config/jest-unit.config.js' );

module.exports = {
	...defaultConfig,
	moduleNameMapper: {
		...defaultConfig.moduleNameMapper,
		'^@wp-plugin-lib/api-fetch/api-method$': '<rootDir>/src/__mocks__/@wp-plugin-lib/api-method.ts',
		'^@wp-plugin-lib/api-fetch$': '<rootDir>/src/__mocks__/@wp-plugin-lib/api-fetch.ts',
		'^@wp-plugin-lib$': '<rootDir>/src/wp-plugin-lib',
		'^@wp-plugin-components$': '<rootDir>/src/wp-plugin-components',
		'^@wp-plugin-lib/(.*)$': '<rootDir>/src/wp-plugin-lib/$1',
		'^@wp-plugin-components/(.*)$': '<rootDir>/src/wp-plugin-components/$1',
		'^lib/(.*)$': '<rootDir>/src/lib/$1',
		'^component/(.*)$': '<rootDir>/src/component/$1',
		'^state/(.*)$': '<rootDir>/src/state/$1',
		'^page/(.*)$': '<rootDir>/src/page/$1',
		'^stores/(.*)$': '<rootDir>/src/stores/$1',
		'^types/(.*)$': '<rootDir>/src/types/$1',
	},
};
