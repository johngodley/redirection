import { getServerUrl, getOrigin } from 'lib/wordpress-url';

// jsdom sets document.location.origin to 'http://localhost' by default
const ORIGIN = 'http://localhost';

// ---------------------------------------------------------------------------
// getServerUrl( domain, path )
// Joins a domain and a path, stripping trailing slash from domain and
// leading slash from path so they never double up.
// ---------------------------------------------------------------------------

describe( 'getServerUrl', () => {
	test( 'joins domain and path', () => {
		expect( getServerUrl( 'https://example.com', '/page' ) ).toBe( 'https://example.com/page' );
	} );

	test( 'strips trailing slash from domain', () => {
		expect( getServerUrl( 'https://example.com/', '/page' ) ).toBe( 'https://example.com/page' );
	} );

	test( 'strips leading slash from path', () => {
		expect( getServerUrl( 'https://example.com', '/page' ) ).toBe( 'https://example.com/page' );
	} );

	test( 'handles path without leading slash', () => {
		expect( getServerUrl( 'https://example.com', 'page' ) ).toBe( 'https://example.com/page' );
	} );

	test( 'handles domain without trailing slash and path without leading slash', () => {
		expect( getServerUrl( 'https://example.com', 'about' ) ).toBe( 'https://example.com/about' );
	} );

	test( 'handles deeply nested path', () => {
		expect( getServerUrl( 'https://example.com', '/a/b/c' ) ).toBe( 'https://example.com/a/b/c' );
	} );

	test( 'handles path with query string', () => {
		expect( getServerUrl( 'https://example.com', '/page?foo=bar' ) ).toBe( 'https://example.com/page?foo=bar' );
	} );
} );

// ---------------------------------------------------------------------------
// getOrigin( domain )
// Returns the origin (protocol + hostname) for a domain value from the DB.
// The domain field is stored as `protocol://hostname` by PHP.
// Falls back to document.location.origin when domain is absent.
// ---------------------------------------------------------------------------

describe( 'getOrigin', () => {
	test( 'returns domain unchanged when it already has https://', () => {
		expect( getOrigin( 'https://example.com' ) ).toBe( 'https://example.com' );
	} );

	test( 'handles http:// protocol', () => {
		expect( getOrigin( 'http://example.com' ) ).toBe( 'http://example.com' );
	} );

	test( 'prepends https:// to a protocol-less domain', () => {
		// Older DB rows may store just the hostname without protocol
		expect( getOrigin( 'example.com' ) ).toBe( 'https://example.com' );
	} );

	test( 'returns document.location.origin when domain is null', () => {
		expect( getOrigin( null ) ).toBe( ORIGIN );
	} );

	test( 'returns document.location.origin when domain is undefined', () => {
		expect( getOrigin( undefined ) ).toBe( ORIGIN );
	} );

	test( 'returns document.location.origin when domain is empty string', () => {
		expect( getOrigin( '' ) ).toBe( ORIGIN );
	} );

	test( 'protocol check is case-insensitive', () => {
		expect( getOrigin( 'HTTPS://example.com' ) ).toBe( 'HTTPS://example.com' );
		expect( getOrigin( 'HTTP://example.com' ) ).toBe( 'HTTP://example.com' );
	} );
} );

// ---------------------------------------------------------------------------
// getServerUrl + getOrigin combined
// Mirrors how columns.tsx builds URLs from the domain field + a relative path.
// ---------------------------------------------------------------------------

describe( 'getServerUrl + getOrigin combined', () => {
	test( 'builds full URL from domain with protocol and relative path', () => {
		expect( getServerUrl( getOrigin( 'https://example.com' ), '/some-page' ) ).toBe(
			'https://example.com/some-page'
		);
	} );

	test( 'builds full URL from protocol-less domain', () => {
		expect( getServerUrl( getOrigin( 'example.com' ), '/some-page' ) ).toBe( 'https://example.com/some-page' );
	} );

	test( 'falls back to document.location.origin when domain is null', () => {
		expect( getServerUrl( getOrigin( null ), '/some-page' ) ).toBe( `${ ORIGIN }/some-page` );
	} );

	test( 'does not double-slash when domain has trailing slash', () => {
		expect( getServerUrl( getOrigin( 'https://example.com/' ), '/page' ) ).toBe( 'https://example.com/page' );
	} );
} );
