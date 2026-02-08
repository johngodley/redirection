/**
 * Internal dependencies
 */

import { getApiNonce, getApiUrl } from '../api-url';

describe( 'API URL', () => {
	test( 'getApiUrl returns a URL', () => {
		expect( getApiUrl() ).toBe( '/wp-json/' );
	} );

	test( 'getApiNonce returns a nonce', () => {
		expect( getApiNonce() ).toBe( 'nonce' );
	})
} );
