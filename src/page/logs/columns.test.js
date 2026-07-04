/**
 * Tests for getUrl and getTarget in page/logs/columns.tsx.
 *
 * These functions build the clickable URLs shown in the log table.
 * The key regression being guarded: absolute targets (https://...) must NOT
 * have the site domain prepended — only relative paths get the domain prepended.
 */

import { getUrl, getTarget } from 'page/logs/columns';

jest.mock( 'lib/wordpress-url', () => ( {
	getServerUrl: ( domain, path ) => domain.replace( /\/$/, '' ) + '/' + path.replace( /^\//, '' ),
	getOrigin: ( domain ) => {
		if ( ! domain ) {
			return 'http://localhost';
		}
		return /^https?:\/\//i.test( domain ) ? domain : 'https://' + domain;
	},
} ) );

// ExternalLink renders as a plain span — we inspect JSX props via result.props
jest.mock( '@wp-plugin-components', () => ( {
	ExternalLink: 'ExternalLink',
} ) );

// ---------------------------------------------------------------------------
// getUrl — builds the clickable href for the source URL column
// ---------------------------------------------------------------------------

describe( 'getUrl', () => {
	test( 'returns empty string when url is absent', () => {
		expect( getUrl( { id: 1 } ) ).toBe( '' );
	} );

	test( 'returns empty string when url is empty string', () => {
		expect( getUrl( { id: 1, url: '' } ) ).toBe( '' );
	} );

	test( 'joins domain and relative url', () => {
		expect( getUrl( { id: 1, url: '/some-page', domain: 'https://example.com' } ) ).toBe(
			'https://example.com/some-page'
		);
	} );

	test( 'falls back to localhost origin when domain is null', () => {
		expect( getUrl( { id: 1, url: '/some-page', domain: null } ) ).toBe( 'http://localhost/some-page' );
	} );

	test( 'falls back to localhost origin when domain is absent', () => {
		expect( getUrl( { id: 1, url: '/some-page' } ) ).toBe( 'http://localhost/some-page' );
	} );

	test( 'handles domain without protocol', () => {
		expect( getUrl( { id: 1, url: '/page', domain: 'example.com' } ) ).toBe( 'https://example.com/page' );
	} );
} );

// ---------------------------------------------------------------------------
// getTarget — builds the clickable href for the target/destination column
// ---------------------------------------------------------------------------

describe( 'getTarget', () => {
	test( 'returns empty string when sent_to is absent', () => {
		expect( getTarget( { id: 1 }, {} ) ).toBe( '' );
	} );

	test( 'returns empty string when sent_to is null', () => {
		expect( getTarget( { id: 1, sent_to: null }, {} ) ).toBe( '' );
	} );

	test( 'returns empty string when sent_to is empty string', () => {
		expect( getTarget( { id: 1, sent_to: '' }, {} ) ).toBe( '' );
	} );

	test( 'uses sent_to directly when it is an absolute https URL', () => {
		const result = getTarget( { id: 1, sent_to: 'https://external.com/page', domain: 'https://example.com' }, {} );
		expect( result.props.url ).toBe( 'https://external.com/page' );
	} );

	test( 'uses sent_to directly when it is an absolute http URL', () => {
		const result = getTarget( { id: 1, sent_to: 'http://external.com/page', domain: 'https://example.com' }, {} );
		expect( result.props.url ).toBe( 'http://external.com/page' );
	} );

	test( 'does NOT prepend domain to an absolute target — regression test', () => {
		// Before the fix this would have returned 'https://example.com/https://external.com/page'
		const result = getTarget( { id: 1, sent_to: 'https://external.com/page', domain: 'https://example.com' }, {} );
		expect( result.props.url ).not.toContain( 'example.com/https' );
	} );

	test( 'prepends domain to a relative target', () => {
		const result = getTarget( { id: 1, sent_to: '/destination', domain: 'https://example.com' }, {} );
		expect( result.props.url ).toBe( 'https://example.com/destination' );
	} );

	test( 'falls back to localhost origin for relative target when domain is null', () => {
		const result = getTarget( { id: 1, sent_to: '/destination', domain: null }, {} );
		expect( result.props.url ).toBe( 'http://localhost/destination' );
	} );

	test( 'absolute URL check is case-insensitive', () => {
		const result = getTarget( { id: 1, sent_to: 'HTTPS://external.com/page', domain: 'https://example.com' }, {} );
		expect( result.props.url ).toBe( 'HTTPS://external.com/page' );
	} );
} );
