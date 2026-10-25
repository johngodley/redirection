const testItem = require( '../util' );

describe( 'Language Redirect Tester', () => {
	const suite = require( './language.json' );

	test.each( suite )( 'Case %#: %s', ( comment, { source, target } ) => {
		return testItem( source, target );
	} );
} );
