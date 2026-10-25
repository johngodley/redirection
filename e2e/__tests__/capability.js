const testItem = require( '../util' );

describe( 'Capability Redirect Tester', () => {
	const suite = require( './capability.json' );

	test.each( suite )( 'Case %#: %s', ( comment, { source, target } ) => {
		return testItem( source, target );
	} );
} );
