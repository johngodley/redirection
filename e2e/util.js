const fetch = require( 'node-fetch' );

const BASE_URL = 'http://redirection-e2e.local';

jest.setTimeout( 20000 );

function testItem( source, target ) {
	return fetch( BASE_URL + source.url, {
		redirect: 'manual',
		headers: source.headers ? source.headers : {},
	} )
		.then( ( { status, headers } ) => {
			expect( status ).toEqual( target.status );

			if ( target.location ) {
				const fullTarget = target.location.indexOf( 'http:' ) === -1 ? BASE_URL + target.location : target.location;

				expect( headers.get( 'location' ) ).toBe( fullTarget );
			}

			if ( target.agent ) {
				expect( headers.get( 'x-redirect-agent' ) ).toBe( 'redirection' );
			}
		} );
}

module.exports = testItem;
