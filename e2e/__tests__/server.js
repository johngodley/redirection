const testItem = require( '../util' );

describe( 'Server Redirect Tester', () => {
	const suite = require( './server.json' );

	test.each( suite )( 'Case %#: %s', ( comment, { source, target } ) => {
		return testItem( source, target );
	} );
} );
