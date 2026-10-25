const testItem = require( '../util' );

describe( 'Login Redirect Tester', () => {
	const suite = require( './login.json' );

	test.each( suite )( 'Case %#: %s', ( comment, { source, target } ) => {
		return testItem( source, target );
	} );
} );
