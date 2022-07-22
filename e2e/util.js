const fetch = require( 'node-fetch' );

const BASE_URL = 'http://redirection-e2e.local';

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
				expect( headers.get( 'x-redirect-by' ) ).toBe( 'redirection' );
			}

			if ( target.headers ) {
				Object.keys( target.headers ).forEach( header => {
					expect( headers.get( header.toLowerCase() ) ).toBe( target.headers[ header ] );
				} );
			}
		} );
}

module.exports = testItem;
