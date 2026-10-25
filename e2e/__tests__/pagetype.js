const testItem = require( '../util' );

describe( 'PageType Redirect Tester', () => {
	const suite = require( './pagetype.json' );

	test.each( suite )( 'Case %#: %s', ( comment, { source, target } ) => {
		return testItem( source, target );
	} );
} );
