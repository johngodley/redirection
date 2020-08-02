import { getApiRequest, postApiRequest } from '../api-method';
import apiFetch from 'wp-plugin-lib/api-fetch';

apiFetch.use( apiFetch.createRootURLMiddleware( '/wp-json/' ) );
apiFetch.use( apiFetch.createNonceMiddleware( 'nonce' ) );

const acceptJson = 'application/json, */*;q=0.1';
const setJson = 'application/json; charset=utf-8';

describe( 'API methods', () => {
	describe( 'GET', () => {
		test( 'get API enables JSON', () => {
			expect( getApiRequest( 'test', {} ).headers.get( 'Accept' ) ).toBe( acceptJson );
		} );

		test( 'get without query params returns url', () => {
			const expected = '/wp-json/search-regex/v1/test/?_wpnonce=nonce';

			expect( getApiRequest( 'test', {} ).url ).toBe( expected );
		} );

		test( 'get with query params returns url with params', () => {
			const expected = '/wp-json/search-regex/v1/test/?cat=1&_wpnonce=nonce';

			expect( getApiRequest( 'test', { cat: 1 } ).url ).toBe( expected );
		} );
	} );

	describe( 'POST', () => {
		test( 'post API sets content type', () => {
			const headers = postApiRequest( 'test', {} ).headers;

			expect( headers.get( 'Accept' ) ).toBe( acceptJson );
			expect( headers.get( 'Content-Type' ) ).toBe( setJson );
		} );

		test( 'post with no body returns {}', () => {
			const expected = '{}';

			expect( postApiRequest( 'test', {} ).body ).toBe( expected );
		} )

		test( 'post with body and query params', () => {
			const expectedBody = JSON.stringify( { cat: 1 } );
			const expectedUrl = '/wp-json/search-regex/v1/test/?param=2&_wpnonce=nonce';
			const request = postApiRequest( 'test', { cat: 1 }, { param: 2 } );

			expect( request.body ).toBe( expectedBody );
			expect( request.url ).toBe( expectedUrl );
		} )
	} );
} );
