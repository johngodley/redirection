const testItem = require( '../util' );

describe( 'Cookie Redirect Tester', () => {
	const suite = require( './cookie.json' );

	test.each( suite )( 'Case %#: %s', ( comment, { source, target } ) => {
		return testItem( source, target );
	} );
} );
