const http = require( 'http' );

const BASE_URL = process.env.E2E_BASE_URL || 'http://localhost:8889';

/**
 * Send the path exactly as given. `fetch` normalises the URL first, such as turning a `\` into a `/`.
 *
 * @param {string} path    Request path.
 * @param {Object} headers Request headers.
 * @return {Promise<{status: number, headers: {get: function(string): (string|null)}}>} Response.
 */
function rawRequest( path, headers ) {
	const { hostname, port } = new URL( BASE_URL );

	return new Promise( ( resolve, reject ) => {
		http.get( { hostname, port, path, headers }, ( response ) => {
			response.resume();
			resolve( {
				status: response.statusCode,
				headers: { get: ( name ) => response.headers[ name ] ?? null },
			} );
		} ).on( 'error', reject );
	} );
}

function testItem( source, target ) {
	const headers = source.headers ? source.headers : {};
	const request = source.raw
		? rawRequest( source.url, headers )
		: fetch( BASE_URL + source.url, { redirect: 'manual', headers } );

	return request.then( ( { status, headers: responseHeaders } ) => {
		expect( status ).toEqual( target.status );

		if ( target.location ) {
			// {home} is the site URL, for redirects that WordPress makes absolute
			expect( responseHeaders.get( 'location' ) ).toBe( target.location.replace( '{home}', BASE_URL ) );
		}

		if ( target.sameOrigin ) {
			expect( responseHeaders.get( 'location' ) ).toBeTruthy();

			// Resolve the location as a browser would, relative to the requested URL
			const location = new URL( responseHeaders.get( 'location' ), BASE_URL + source.url );

			expect( location.origin ).toBe( new URL( BASE_URL ).origin );
		}

		if ( target.agent ) {
			expect( responseHeaders.get( 'x-redirect-by' ) ).toBe( 'redirection' );
		}

		if ( target.headers ) {
			Object.keys( target.headers ).forEach( ( header ) => {
				expect( responseHeaders.get( header.toLowerCase() ) ).toBe( target.headers[ header ] );
			} );
		}
	} );
}

module.exports = testItem;
