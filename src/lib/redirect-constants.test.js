import {
	getMatchState,
	hasUrlTarget,
	ACTION_URL,
	ACTION_RANDOM,
	ACTION_PASS,
	ACTION_ERROR,
	ACTION_NOTHING,
} from 'lib/redirect-constants';

const URL_FROM = 'https://example.com/matched';
const URL_NOTFROM = 'https://example.com/unmatched';

const withUrlFromNotfrom = ( extra ) => ( {
	url_from: URL_FROM,
	url_notfrom: URL_NOTFROM,
	...extra,
} );

describe( 'hasUrlTarget', () => {
	test( 'pass-through actions have a URL target', () => {
		expect( hasUrlTarget( ACTION_PASS ) ).toBe( true );
	} );

	test( 'redirect to URL actions have a URL target', () => {
		expect( hasUrlTarget( ACTION_URL ) ).toBe( true );
	} );

	test( 'random actions do not have a URL target', () => {
		expect( hasUrlTarget( ACTION_RANDOM ) ).toBe( false );
	} );

	test( 'error actions do not have a URL target', () => {
		expect( hasUrlTarget( ACTION_ERROR ) ).toBe( false );
	} );

	test( 'do-nothing actions do not have a URL target', () => {
		expect( hasUrlTarget( ACTION_NOTHING ) ).toBe( false );
	} );
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
			[ 'ip', { ip: [ '1.2.3.4' ] } ],
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

	describe( 'ip match type uses string[] for ip field', () => {
		test( 'preserves an existing ip array', () => {
			const state = getMatchState( 'ip', { ip: [ '1.2.3.4', '5.6.7.8' ] } );
			expect( state.ip ).toEqual( [ '1.2.3.4', '5.6.7.8' ] );
		} );

		test( 'wraps a scalar ip string in an array', () => {
			const state = getMatchState( 'ip', { ip: '1.2.3.4' } );
			expect( state.ip ).toEqual( [ '1.2.3.4' ] );
		} );

		test( 'defaults ip to an empty array when absent', () => {
			const state = getMatchState( 'ip', {} );
			expect( state.ip ).toEqual( [] );
		} );
	} );

	describe( 'url match type falls through to raw data', () => {
		test( 'returns data as-is for url match type', () => {
			const data = { url: '/target' };
			const state = getMatchState( 'url', data );

			expect( state ).toEqual( data );
		} );
	} );

	// ---------------------------------------------------------------------------
	// Round-trip tests — simulate what PHP serialises to action_data and verify
	// getMatchState normalises each match type without silently dropping fields.
	// Each fixture mirrors what the PHP REST endpoint actually returns.
	// ---------------------------------------------------------------------------

	describe( 'round-trip: PHP action_data → getMatchState (no fields dropped)', () => {
		describe( 'login', () => {
			test( 'full round-trip with both targets', () => {
				const apiData = { logged_in: '/dashboard', logged_out: '/login' };
				const state = getMatchState( 'login', apiData );

				expect( state.logged_in ).toBe( '/dashboard' );
				expect( state.logged_out ).toBe( '/login' );
				expect( Object.keys( state ) ).toEqual( [ 'logged_in', 'logged_out' ] );
			} );

			test( 'partial data: only logged_in set', () => {
				const state = getMatchState( 'login', { logged_in: '/dashboard', logged_out: '' } );
				expect( state.logged_in ).toBe( '/dashboard' );
				expect( state.logged_out ).toBe( '' );
			} );

			test( 'null data returns empty strings', () => {
				const state = getMatchState( 'login', null );
				expect( state.logged_in ).toBe( '' );
				expect( state.logged_out ).toBe( '' );
			} );
		} );

		describe( 'referrer', () => {
			test( 'full round-trip', () => {
				const apiData = {
					referrer: 'https://google.com',
					regex: true,
					url_from: '/matched',
					url_notfrom: '/unmatched',
				};
				const state = getMatchState( 'referrer', apiData );

				expect( state.referrer ).toBe( 'https://google.com' );
				expect( state.regex ).toBe( true );
				expect( state.url_from ).toBe( '/matched' );
				expect( state.url_notfrom ).toBe( '/unmatched' );
				expect( Object.keys( state ) ).toEqual( [ 'referrer', 'regex', 'url_from', 'url_notfrom' ] );
			} );

			test( 'regex defaults to false when absent', () => {
				const state = getMatchState( 'referrer', { referrer: 'https://example.com' } );
				expect( state.regex ).toBe( false );
			} );

			test( 'null data returns all empty/false defaults', () => {
				const state = getMatchState( 'referrer', null );
				expect( state ).toEqual( { referrer: '', regex: false, url_from: '', url_notfrom: '' } );
			} );
		} );

		describe( 'agent', () => {
			test( 'full round-trip', () => {
				const apiData = {
					agent: 'Googlebot',
					regex: false,
					url_from: '/bot-matched',
					url_notfrom: '',
				};
				const state = getMatchState( 'agent', apiData );

				expect( state.agent ).toBe( 'Googlebot' );
				expect( state.regex ).toBe( false );
				expect( state.url_from ).toBe( '/bot-matched' );
				expect( state.url_notfrom ).toBe( '' );
				expect( Object.keys( state ) ).toEqual( [ 'agent', 'regex', 'url_from', 'url_notfrom' ] );
			} );

			test( 'null data returns all empty/false defaults', () => {
				const state = getMatchState( 'agent', null );
				expect( state ).toEqual( { agent: '', regex: false, url_from: '', url_notfrom: '' } );
			} );
		} );

		describe( 'cookie', () => {
			test( 'full round-trip', () => {
				const apiData = {
					name: 'session_cookie',
					value: 'expected_value',
					regex: true,
					url_from: '/cookie-matched',
					url_notfrom: '/cookie-unmatched',
				};
				const state = getMatchState( 'cookie', apiData );

				expect( state.name ).toBe( 'session_cookie' );
				expect( state.value ).toBe( 'expected_value' );
				expect( state.regex ).toBe( true );
				expect( state.url_from ).toBe( '/cookie-matched' );
				expect( state.url_notfrom ).toBe( '/cookie-unmatched' );
				expect( Object.keys( state ) ).toEqual( [ 'name', 'value', 'regex', 'url_from', 'url_notfrom' ] );
			} );

			test( 'null data returns all empty/false defaults', () => {
				const state = getMatchState( 'cookie', null );
				expect( state ).toEqual( { name: '', value: '', regex: false, url_from: '', url_notfrom: '' } );
			} );
		} );

		describe( 'header', () => {
			test( 'full round-trip', () => {
				const apiData = {
					name: 'X-Custom-Header',
					value: 'expected',
					regex: false,
					url_from: '/header-matched',
					url_notfrom: '',
				};
				const state = getMatchState( 'header', apiData );

				expect( state.name ).toBe( 'X-Custom-Header' );
				expect( state.value ).toBe( 'expected' );
				expect( state.regex ).toBe( false );
				expect( Object.keys( state ) ).toEqual( [ 'name', 'value', 'regex', 'url_from', 'url_notfrom' ] );
			} );

			test( 'null data returns all empty/false defaults', () => {
				const state = getMatchState( 'header', null );
				expect( state ).toEqual( { name: '', value: '', regex: false, url_from: '', url_notfrom: '' } );
			} );
		} );

		describe( 'custom', () => {
			test( 'full round-trip', () => {
				const apiData = {
					filter: 'my_custom_filter',
					url_from: '/custom-matched',
					url_notfrom: '/custom-unmatched',
				};
				const state = getMatchState( 'custom', apiData );

				expect( state.filter ).toBe( 'my_custom_filter' );
				expect( state.url_from ).toBe( '/custom-matched' );
				expect( state.url_notfrom ).toBe( '/custom-unmatched' );
				expect( Object.keys( state ) ).toEqual( [ 'filter', 'url_from', 'url_notfrom' ] );
			} );

			test( 'null data returns empty defaults', () => {
				const state = getMatchState( 'custom', null );
				expect( state ).toEqual( { filter: '', url_from: '', url_notfrom: '' } );
			} );
		} );

		describe( 'role', () => {
			test( 'full round-trip', () => {
				const apiData = {
					role: 'administrator',
					url_from: '/admin-page',
					url_notfrom: '/public-page',
				};
				const state = getMatchState( 'role', apiData );

				expect( state.role ).toBe( 'administrator' );
				expect( state.url_from ).toBe( '/admin-page' );
				expect( state.url_notfrom ).toBe( '/public-page' );
				expect( Object.keys( state ) ).toEqual( [ 'role', 'url_from', 'url_notfrom' ] );
			} );

			test( 'null data returns empty defaults', () => {
				const state = getMatchState( 'role', null );
				expect( state ).toEqual( { role: '', url_from: '', url_notfrom: '' } );
			} );
		} );

		describe( 'server', () => {
			test( 'full round-trip', () => {
				const apiData = {
					server: 'example.com',
					url_from: '/server-matched',
					url_notfrom: '',
				};
				const state = getMatchState( 'server', apiData );

				expect( state.server ).toBe( 'example.com' );
				expect( state.url_from ).toBe( '/server-matched' );
				expect( Object.keys( state ) ).toEqual( [ 'server', 'url_from', 'url_notfrom' ] );
			} );

			test( 'null data returns empty defaults', () => {
				const state = getMatchState( 'server', null );
				expect( state ).toEqual( { server: '', url_from: '', url_notfrom: '' } );
			} );
		} );

		describe( 'ip', () => {
			test( 'full round-trip with array ip (PHP serialises as JSON array)', () => {
				const apiData = {
					ip: [ '192.168.1.1', '10.0.0.1' ],
					url_from: '/ip-matched',
					url_notfrom: '',
				};
				const state = getMatchState( 'ip', apiData );

				expect( state.ip ).toEqual( [ '192.168.1.1', '10.0.0.1' ] );
				expect( state.url_from ).toBe( '/ip-matched' );
				expect( Object.keys( state ) ).toEqual( [ 'ip', 'url_from', 'url_notfrom' ] );
			} );

			test( 'scalar ip string is normalised to array (legacy PHP data)', () => {
				// Older PHP versions may serialize ip as a plain string
				const state = getMatchState( 'ip', { ip: '192.168.1.1' } );
				expect( state.ip ).toEqual( [ '192.168.1.1' ] );
			} );

			test( 'null data returns empty array for ip', () => {
				const state = getMatchState( 'ip', null );
				expect( state.ip ).toEqual( [] );
			} );
		} );

		describe( 'language', () => {
			test( 'full round-trip', () => {
				const apiData = {
					language: 'en_US',
					url_from: '/en-page',
					url_notfrom: '/fr-page',
				};
				const state = getMatchState( 'language', apiData );

				expect( state.language ).toBe( 'en_US' );
				expect( state.url_from ).toBe( '/en-page' );
				expect( state.url_notfrom ).toBe( '/fr-page' );
				expect( Object.keys( state ) ).toEqual( [ 'language', 'url_from', 'url_notfrom' ] );
			} );

			test( 'null data returns empty defaults', () => {
				const state = getMatchState( 'language', null );
				expect( state ).toEqual( { language: '', url_from: '', url_notfrom: '' } );
			} );
		} );

		describe( 'url (default pass-through)', () => {
			test( 'returns raw data unchanged', () => {
				const apiData = { some_flag: true, other: 'value' };
				const state = getMatchState( 'url', apiData );
				expect( state ).toEqual( apiData );
			} );

			test( 'returns empty object when data is null', () => {
				const state = getMatchState( 'url', null );
				expect( state ).toEqual( {} );
			} );

			test( 'returns empty object when data is undefined', () => {
				const state = getMatchState( 'url', undefined );
				expect( state ).toEqual( {} );
			} );
		} );

		describe( 'unknown match type (fall-through)', () => {
			test( 'returns raw data unchanged for unrecognised type', () => {
				const apiData = { foo: 'bar' };
				const state = getMatchState( 'unknown_type', apiData );
				expect( state ).toEqual( apiData );
			} );

			test( 'returns empty object for unknown type with null data', () => {
				const state = getMatchState( 'unknown_type', null );
				expect( state ).toEqual( {} );
			} );
		} );
	} );
} );
