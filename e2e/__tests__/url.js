const testItem = require( '../util' );

describe( 'URL Redirect Tester', () => {
	const suite = require( './url.json' );

	test.each( suite )( 'Case %#: %s', ( comment, { source, target } ) => {
		return testItem( source, target );
	} );
} );
