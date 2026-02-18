import { getMatchState } from 'lib/redirect-constants';

const URL_FROM = 'https://example.com/matched';
const URL_NOTFROM = 'https://example.com/unmatched';

const withUrlFromNotfrom = ( extra ) => ( {
	url_from: URL_FROM,
	url_notfrom: URL_NOTFROM,
	...extra,
} );

describe( 'getMatchState', () => {
	describe( 'url_from and url_notfrom are preserved for all applicable match types', () => {
		const types = [
			[ 'referrer', { referrer: 'https://referrer.com', regex: false } ],
			[ 'agent', { agent: 'Mozilla', regex: false } ],
			[ 'cookie', { name: 'my_cookie', value: 'val', regex: false } ],
			[ 'header', { name: 'X-Header', value: 'val', regex: false } ],
			[ 'custom', { filter: 'my_filter' } ],
			[ 'role', { role: 'editor' } ],
			[ 'server', { server: 'example.com' } ],
			[ 'ip', { ip: '1.2.3.4' } ],
			[ 'language', { language: 'en' } ],
		];

		test.each( types )( '%s: preserves url_from and url_notfrom', ( matchType, extra ) => {
			const data = withUrlFromNotfrom( extra );
			const state = getMatchState( matchType, data );

			expect( state.url_from ).toBe( URL_FROM );
			expect( state.url_notfrom ).toBe( URL_NOTFROM );
		} );

		test.each( types )( '%s: defaults url_from and url_notfrom to empty string when absent', ( matchType ) => {
			const state = getMatchState( matchType, {} );

			expect( state.url_from ).toBe( '' );
			expect( state.url_notfrom ).toBe( '' );
		} );
	} );

	describe( 'login match type does not include url_from/url_notfrom', () => {
		test( 'login uses logged_in and logged_out instead', () => {
			const state = getMatchState( 'login', { logged_in: '/in', logged_out: '/out' } );

			expect( state ).toEqual( { logged_in: '/in', logged_out: '/out' } );
			expect( state.url_from ).toBeUndefined();
			expect( state.url_notfrom ).toBeUndefined();
		} );
	} );

	describe( 'url match type falls through to raw data', () => {
		test( 'returns data as-is for url match type', () => {
			const data = { url: '/target' };
			const state = getMatchState( 'url', data );

			expect( state ).toEqual( data );
		} );
	} );
} );
