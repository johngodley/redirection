const testItem = require( '../util' );

describe( 'Referrer Redirect Tester', () => {
	const suite = require( './referrer.json' );

	test.each( suite )( 'Case %#: %s', ( comment, { source, target } ) => {
		return testItem( source, target );
	} );
} );
