const testItem = require( '../util' );

describe( 'Regex Target Redirect Tester', () => {
	const suite = require( './regex-target.json' );

	test.each( suite )( 'Case %#: %s', ( comment, { source, target } ) => {
		return testItem( source, target );
	} );
} );
