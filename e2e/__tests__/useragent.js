const testItem = require( '../util' );

describe( 'UserAgent Redirect Tester', () => {
	const suite = require( './useragent.json' );

	test.each( suite )( 'Case %#: %s', ( comment, { source, target } ) => {
		return testItem( source, target );
	} );
} );
