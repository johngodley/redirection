/**
 * Internal dependencies
 */

import {
	getDefaultItem,
	getMatchState,
	hasUrlTarget,
	hasTargetData,
	getCodeForActionType,
	ACTION_URL,
	ACTION_PASS,
	ACTION_ERROR,
	ACTION_RANDOM,
	ACTION_NOTHING,
	MATCH_URL,
	MATCH_PAGE,
	MATCH_LOGIN,
	MATCH_AGENT,
	MATCH_REFERRER,
	MATCH_COOKIE,
	MATCH_HEADER,
	MATCH_CUSTOM,
	MATCH_ROLE,
	MATCH_SERVER,
	MATCH_IP,
} from '../selector';

describe( 'redirects selector', () => {
	describe( 'getCodeForActionType', () => {
		test( '301 HTTP code for url and pass', () => {
			expect( getCodeForActionType( ACTION_URL ) ).toBe( 301 );
			expect( getCodeForActionType( ACTION_PASS ) ).toBe( 301 );
		} );

		test( '404 HTTP code for error', () => {
			expect( getCodeForActionType( ACTION_ERROR ) ).toBe( 404 );
		} );

		test( 'No HTTP code for other action types', () => {
			expect( getCodeForActionType( ACTION_RANDOM ) ).toBe( 0 );
			expect( getCodeForActionType( ACTION_NOTHING ) ).toBe( 0 );
			expect( getCodeForActionType( 'cat' ) ).toBe( 0 );
		} );
	} );

	describe( 'hasTargetData', () => {
		test( 'return true if URL match has a value', () => {
			expect( hasTargetData( MATCH_URL, { url: 'value' } ) ).toBe( true );
		} );

		test( 'return false if URL match has no value', () => {
			expect( hasTargetData( MATCH_URL, { url: '' } ) ).toBe( false );
		} );

		test( 'return true if page match has a value', () => {
			expect( hasTargetData( MATCH_PAGE, { url: 'value' } ) ).toBe( true );
		} );

		test( 'return false if page match has no value', () => {
			expect( hasTargetData( MATCH_PAGE, { url: '' } ) ).toBe( false );
		} );

		test( 'return true if login match has a value', () => {
			expect( hasTargetData( MATCH_LOGIN, { logged_in: 'value' } ) ).toBe( true );
			expect( hasTargetData( MATCH_LOGIN, { logged_out: 'value' } ) ).toBe( true );
			expect( hasTargetData( MATCH_LOGIN, { logged_in: 'value', logged_out: 'value' } ) ).toBe( true );
		} );

		test( 'return false if login match has no value', () => {
			expect( hasTargetData( MATCH_LOGIN, { logged_in: '', logged_out: '' } ) ).toBe( false );
		} );

		test( 'return true if from/notfrom match has a value', () => {
			expect( hasTargetData( MATCH_AGENT, { url_from: 'value', url_notfrom: 'value' } ) ).toBe( true );
			expect( hasTargetData( MATCH_AGENT, { url_from: 'value', url_notfrom: '' } ) ).toBe( true );
			expect( hasTargetData( MATCH_AGENT, { url_from: '', url_notfrom: 'value' } ) ).toBe( true );
		} );

		test( 'return false if from/notfrom match has no value', () => {
			expect( hasTargetData( MATCH_AGENT, { url_from: '', url_notfrom: '' } ) ).toBe( false );
		} );
	} );

	describe( 'hasUrlTarget', () => {
		test( 'hasUrlTarget returns true if URL or pass-through', () => {
			expect( hasUrlTarget( ACTION_URL ) ).toBe( true );
			expect( hasUrlTarget( ACTION_PASS ) ).toBe( true );
		} );

		test( 'hasUrlTarget returns false if not URL or pass-through', () => {
			expect( hasUrlTarget( ACTION_ERROR ) ).toBe( false );
		} );
	} );

	describe( 'getDefaultItem', () => {
		test( 'getDefaultItem returns a new redirect', () => {
			const source = {
				flag_case: true,
			};
			const expected = {
				id: 0,
				url: 'url',
				match_type: MATCH_URL,
				action_type: ACTION_URL,
				action_data: {
					url: '',
				},
				group_id: 5,
				title: '',
				action_code: 301,
				position: 0,
				match_data: { source },
			};

			expect( getDefaultItem( 'url', 5, source ) ).toEqual( expected );
		} );
	} );

	describe( 'getMatchState', () => {
		test( 'get state for MATCH_URL when empty', () => {
			expect( getMatchState( MATCH_URL, {} ) ).toEqual( { url: '' } );
		} );

		test( 'get state for MATCH_URL when has value', () => {
			expect( getMatchState( MATCH_URL, { url: 'cat' } ) ).toEqual( { url: 'cat' } );
		} );

		test( 'get state for MATCH_LOGIN when empty', () => {
			expect( getMatchState( MATCH_LOGIN, {} ) ).toEqual( { logged_in: '', logged_out: '' } );
		} );

		test( 'get state for MATCH_LOGIN when has value', () => {
			expect( getMatchState( MATCH_LOGIN, { logged_in: 'in', logged_out: 'out' } ) ).toEqual( { logged_in: 'in', logged_out: 'out' } );
		} );

		test( 'get state for MATCH_REFERRER when empty', () => {
			expect( getMatchState( MATCH_REFERRER, {} ) ).toEqual( { url_from: '', url_notfrom: '', referrer: '', regex: false } );
		} );

		test( 'get state for MATCH_REFERRER when has value', () => {
			const state = { url_from: 'from', url_notfrom: 'notfrom', referrer: 'cat', regex: true };
			expect( getMatchState( MATCH_REFERRER, state ) ).toEqual( state );
		} );

		test( 'get state for MATCH_AGENT when empty', () => {
			expect( getMatchState( MATCH_AGENT, {} ) ).toEqual( { url_from: '', url_notfrom: '', agent: '', regex: false } );
		} );

		test( 'get state for MATCH_AGENT when has value', () => {
			const state = { url_from: 'from', url_notfrom: 'notfrom', agent: 'cat', regex: true };
			expect( getMatchState( MATCH_AGENT, state ) ).toEqual( state );
		} );

		test( 'get state for MATCH_COOKIE when empty', () => {
			expect( getMatchState( MATCH_COOKIE, {} ) ).toEqual( { url_from: '', url_notfrom: '', name: '', value: '', regex: false } );
		} );

		test( 'get state for MATCH_COOKIE when has value', () => {
			const state = { url_from: 'from', url_notfrom: 'notfrom', name: 'cat', value: 'dog', regex: true };
			expect( getMatchState( MATCH_COOKIE, state ) ).toEqual( state );
		} );

		test( 'get state for MATCH_HEADER when empty', () => {
			expect( getMatchState( MATCH_HEADER, {} ) ).toEqual( { url_from: '', url_notfrom: '', name: '', value: '', regex: false } );
		} );

		test( 'get state for MATCH_HEADER when has value', () => {
			const state = { url_from: 'from', url_notfrom: 'notfrom', name: 'cat', value: 'dog', regex: true };
			expect( getMatchState( MATCH_HEADER, state ) ).toEqual( state );
		} );

		test( 'get state for MATCH_CUSTOM when empty', () => {
			expect( getMatchState( MATCH_CUSTOM, {} ) ).toEqual( { url_from: '', url_notfrom: '', filter: '' } );
		} );

		test( 'get state for MATCH_CUSTOM when has value', () => {
			const state = { url_from: 'from', url_notfrom: 'notfrom', filter: 'cat' };
			expect( getMatchState( MATCH_CUSTOM, state ) ).toEqual( state );
		} );

		test( 'get state for MATCH_ROLE when empty', () => {
			expect( getMatchState( MATCH_ROLE, {} ) ).toEqual( { url_from: '', url_notfrom: '', role: '' } );
		} );

		test( 'get state for MATCH_ROLE when has value', () => {
			const state = { url_from: 'from', url_notfrom: 'notfrom', role: 'cat' };
			expect( getMatchState( MATCH_ROLE, state ) ).toEqual( state );
		} );

		test( 'get state for MATCH_SERVER when empty', () => {
			expect( getMatchState( MATCH_SERVER, {} ) ).toEqual( { url_from: '', url_notfrom: '', server: '' } );
		} );

		test( 'get state for MATCH_SERVER when has value', () => {
			const state = { url_from: 'from', url_notfrom: 'notfrom', server: 'cat' };
			expect( getMatchState( MATCH_SERVER, state ) ).toEqual( state );
		} );

		test( 'get state for MATCH_IP when empty', () => {
			expect( getMatchState( MATCH_IP, {} ) ).toEqual( { url_from: '', url_notfrom: '', ip: [] } );
		} );

		test( 'get state for MATCH_IP when has value', () => {
			const state = { url_from: 'from', url_notfrom: 'notfrom', ip: [ 'cat' ] };
			expect( getMatchState( MATCH_IP, state ) ).toEqual( state );
		} );

		test( 'get state for MATCH_PAGE when empty', () => {
			expect( getMatchState( MATCH_PAGE, {} ) ).toEqual( { page: '404', url: '' } );
		} );

		test( 'get state for MATCH_PAGE when has value', () => {
			const state = { page: 405, url: 'cat' };
			expect( getMatchState( MATCH_PAGE, state ) ).toEqual( state );
		} );
	} );
} );
