const testItem = require( '../util' );

describe( 'HTTP Redirect Tester', () => {
	const suite = require( './http.json' );

	test.each( suite )( 'Case %#: %s', ( comment, { source, target } ) => {
		return testItem( source, target );
	} );
} );
