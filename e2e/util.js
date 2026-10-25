const BASE_URL = process.env.E2E_BASE_URL || 'http://localhost:8889';

function testItem( source, target ) {
	return fetch( BASE_URL + source.url, {
		redirect: 'manual',
		headers: source.headers ? source.headers : {},
	} ).then( ( { status, headers } ) => {
		expect( status ).toEqual( target.status );

		if ( target.location ) {
			// {home} is the site URL, for redirects that WordPress makes absolute
			expect( headers.get( 'location' ) ).toBe( target.location.replace( '{home}', BASE_URL ) );
		}

		if ( target.agent ) {
			expect( headers.get( 'x-redirect-by' ) ).toBe( 'redirection' );
		}

		if ( target.headers ) {
			Object.keys( target.headers ).forEach( ( header ) => {
				expect( headers.get( header.toLowerCase() ) ).toBe( target.headers[ header ] );
			} );
		}
	} );
}

module.exports = testItem;
