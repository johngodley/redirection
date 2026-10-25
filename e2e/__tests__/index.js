const testItem = require( '../util' );

describe( 'GeneralRedirect Tester', () => {
	const suite = require( './general.json' );

	test.each( suite )( 'Case %#: %s', ( comment, { source, target } ) => {
		return testItem( source, target );
	} );
} );
