const testItem = require( '../util' );

describe( 'IP Redirect Tester', () => {
	const suite = require( './ip.json' );

	test.each( suite )( 'Case %#: %s', ( comment, { source, target } ) => {
		return testItem( source, target );
	} );
} );
