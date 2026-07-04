/**
 * Zod schema smoke tests for log and 404 API responses.
 *
 * These tests parse fixture data that mirrors what the PHP API returns,
 * catching any mismatch between what the backend sends and what the
 * frontend schemas expect. Each fixture is modelled on an actual DB row
 * (or grouped aggregate row) as serialised by the PHP REST endpoint.
 */

import { LogSchema, Error404Schema, LogListResponseSchema, Error404ListResponseSchema } from 'types/schemas/log';

// ---------------------------------------------------------------------------
// Fixture factories — mirror what PHP serialises to JSON
// ---------------------------------------------------------------------------

const makeLog = ( overrides = {} ) => ( {
	id: 1,
	created: '2026-01-15 10:30:00',
	url: '/some-page',
	sent_to: '/destination',
	agent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
	referrer: 'https://google.com',
	ip: '192.168.1.1',
	domain: 'https://example.com',
	redirect_id: 0,
	redirection_id: 42,
	request_method: 'GET',
	http_code: 301,
	request_data: null,
	redirect_by: 'Redirection',
	...overrides,
} );

const makeLog404 = ( overrides = {} ) => ( {
	id: 5,
	created: '2026-01-15 10:30:00',
	created_time: '10:30:00',
	url: '/not-found',
	agent: 'Mozilla/5.0',
	referrer: 'https://referrer.example.com',
	domain: 'https://example.com',
	ip: '10.0.0.1',
	http_code: 404,
	request_method: 'GET',
	request_data: null,
	...overrides,
} );

const makeLogListResponse = ( items = [], total = items.length ) => ( {
	items,
	total,
} );

// ---------------------------------------------------------------------------
// LogSchema — individual entries
// ---------------------------------------------------------------------------

describe( 'LogSchema', () => {
	describe( 'individual log entries', () => {
		test( 'parses a complete log entry', () => {
			expect( () => LogSchema.parse( makeLog() ) ).not.toThrow();
		} );

		test( 'id can be a number (individual entry)', () => {
			const result = LogSchema.parse( makeLog( { id: 7 } ) );
			expect( result.id ).toBe( 7 );
		} );

		test( 'id can be a string (grouped result)', () => {
			const result = LogSchema.parse( makeLog( { id: '/some-url' } ) );
			expect( result.id ).toBe( '/some-url' );
		} );

		test( 'rejects null id', () => {
			expect( () => LogSchema.parse( makeLog( { id: null } ) ) ).toThrow();
		} );

		test( 'agent can be null', () => {
			const result = LogSchema.parse( makeLog( { agent: null } ) );
			expect( result.agent ).toBeNull();
		} );

		test( 'referrer can be null', () => {
			const result = LogSchema.parse( makeLog( { referrer: null } ) );
			expect( result.referrer ).toBeNull();
		} );

		test( 'ip can be null', () => {
			const result = LogSchema.parse( makeLog( { ip: null } ) );
			expect( result.ip ).toBeNull();
		} );

		test( 'domain can be null', () => {
			const result = LogSchema.parse( makeLog( { domain: null } ) );
			expect( result.domain ).toBeNull();
		} );

		test( 'sent_to can be null', () => {
			const result = LogSchema.parse( makeLog( { sent_to: null } ) );
			expect( result.sent_to ).toBeNull();
		} );

		test( 'request_method can be null', () => {
			const result = LogSchema.parse( makeLog( { request_method: null } ) );
			expect( result.request_method ).toBeNull();
		} );

		test( 'request_data can be null', () => {
			const result = LogSchema.parse( makeLog( { request_data: null } ) );
			expect( result.request_data ).toBeNull();
		} );

		test( 'request_data can be an object', () => {
			const result = LogSchema.parse( makeLog( { request_data: { method: 'POST', body: 'foo=bar' } } ) );
			expect( result.request_data ).toEqual( { method: 'POST', body: 'foo=bar' } );
		} );

		test( 'redirect_by can be null', () => {
			const result = LogSchema.parse( makeLog( { redirect_by: null } ) );
			expect( result.redirect_by ).toBeNull();
		} );

		test( 'optional fields can be absent', () => {
			const minimal = { id: 1 };
			expect( () => LogSchema.parse( minimal ) ).not.toThrow();
		} );
	} );

	describe( 'grouped log entries', () => {
		test( 'grouped-by-url row has string id and count', () => {
			const grouped = makeLog( {
				id: '/group-url', // string — the grouped value
				count: 5,
				// Individual fields absent in grouped rows
				created: undefined,
				sent_to: undefined,
				agent: undefined,
				referrer: undefined,
				ip: undefined,
			} );
			const result = LogSchema.parse( grouped );
			expect( result.id ).toBe( '/group-url' );
			expect( result.count ).toBe( 5 );
		} );

		test( 'grouped-by-agent row with empty string id (null agent group)', () => {
			// PHP returns '' when agent IS NULL and groupBy=agent
			const grouped = makeLog( { id: '', count: 3 } );
			const result = LogSchema.parse( grouped );
			expect( result.id ).toBe( '' );
			expect( result.count ).toBe( 3 );
		} );

		test( 'count is coerced from string to number', () => {
			// MySQL sometimes returns aggregates as strings
			const grouped = makeLog( { id: '10.0.0.1', count: '8' } );
			const result = LogSchema.parse( grouped );
			expect( result.count ).toBe( 8 );
		} );
	} );
} );

// ---------------------------------------------------------------------------
// Error404Schema — individual and grouped entries
// ---------------------------------------------------------------------------

describe( 'Error404Schema', () => {
	describe( 'individual 404 entries', () => {
		test( 'parses a complete 404 entry', () => {
			expect( () => Error404Schema.parse( makeLog404() ) ).not.toThrow();
		} );

		test( 'id is numeric for individual entries', () => {
			const result = Error404Schema.parse( makeLog404( { id: 12 } ) );
			expect( result.id ).toBe( 12 );
		} );

		test( 'agent can be null', () => {
			const result = Error404Schema.parse( makeLog404( { agent: null } ) );
			expect( result.agent ).toBeNull();
		} );

		test( 'ip can be null', () => {
			const result = Error404Schema.parse( makeLog404( { ip: null } ) );
			expect( result.ip ).toBeNull();
		} );

		test( 'referrer can be null', () => {
			const result = Error404Schema.parse( makeLog404( { referrer: null } ) );
			expect( result.referrer ).toBeNull();
		} );

		test( 'domain can be null', () => {
			const result = Error404Schema.parse( makeLog404( { domain: null } ) );
			expect( result.domain ).toBeNull();
		} );

		test( 'request_method can be null', () => {
			const result = Error404Schema.parse( makeLog404( { request_method: null } ) );
			expect( result.request_method ).toBeNull();
		} );

		test( 'request_data can be null', () => {
			const result = Error404Schema.parse( makeLog404( { request_data: null } ) );
			expect( result.request_data ).toBeNull();
		} );

		test( 'rejects null id — this was the original bug', () => {
			// Before the fix, PHP returned null for id when agent was NULL in groupBy=agent
			expect( () => Error404Schema.parse( makeLog404( { id: null } ) ) ).toThrow();
		} );

		test( 'optional fields can be absent', () => {
			const minimal = { id: 1 };
			expect( () => Error404Schema.parse( minimal ) ).not.toThrow();
		} );
	} );

	describe( 'grouped 404 entries', () => {
		test( 'grouped-by-url row has string id and count', () => {
			const grouped = makeLog404( { id: '/not-found', count: 7 } );
			const result = Error404Schema.parse( grouped );
			expect( result.id ).toBe( '/not-found' );
			expect( result.count ).toBe( 7 );
		} );

		test( 'grouped-by-ip row has string id', () => {
			const grouped = makeLog404( { id: '10.0.0.1', count: 2 } );
			const result = Error404Schema.parse( grouped );
			expect( result.id ).toBe( '10.0.0.1' );
		} );

		test( 'grouped-by-agent with null agent uses empty string id', () => {
			// PHP sets id = '' when agent IS NULL (fixed bug — was returning null before)
			const grouped = makeLog404( { id: '', count: 4 } );
			const result = Error404Schema.parse( grouped );
			expect( result.id ).toBe( '' );
		} );

		test( 'count is coerced from string to number', () => {
			const grouped = makeLog404( { id: '/page', count: '12' } );
			const result = Error404Schema.parse( grouped );
			expect( result.count ).toBe( 12 );
		} );
	} );
} );

// ---------------------------------------------------------------------------
// LogListResponseSchema — paginated envelope
// ---------------------------------------------------------------------------

describe( 'LogListResponseSchema', () => {
	test( 'parses an empty list', () => {
		const result = LogListResponseSchema.parse( makeLogListResponse() );
		expect( result.items ).toHaveLength( 0 );
		expect( result.total ).toBe( 0 );
	} );

	test( 'parses a list with multiple entries', () => {
		const response = makeLogListResponse(
			[ makeLog( { id: 1 } ), makeLog( { id: 2, agent: null, referrer: null } ) ],
			2
		);
		const result = LogListResponseSchema.parse( response );
		expect( result.items ).toHaveLength( 2 );
		expect( result.items[ 1 ].agent ).toBeNull();
	} );

	test( 'parses a grouped response with string ids', () => {
		const response = makeLogListResponse(
			[ makeLog( { id: '/url-a', count: 10 } ), makeLog( { id: '/url-b', count: 3 } ) ],
			2
		);
		const result = LogListResponseSchema.parse( response );
		expect( result.items[ 0 ].id ).toBe( '/url-a' );
		expect( result.items[ 0 ].count ).toBe( 10 );
	} );

	test( 'rejects response with missing total', () => {
		expect( () => LogListResponseSchema.parse( { items: [] } ) ).toThrow();
	} );

	test( 'rejects response with missing items', () => {
		expect( () => LogListResponseSchema.parse( { total: 0 } ) ).toThrow();
	} );

	test( 'rejects an item with null id inside the list', () => {
		const response = makeLogListResponse( [ makeLog( { id: null } ) ], 1 );
		expect( () => LogListResponseSchema.parse( response ) ).toThrow();
	} );
} );

// ---------------------------------------------------------------------------
// Error404ListResponseSchema — paginated envelope
// ---------------------------------------------------------------------------

describe( 'Error404ListResponseSchema', () => {
	test( 'parses an empty list', () => {
		const result = Error404ListResponseSchema.parse( makeLogListResponse() );
		expect( result.items ).toHaveLength( 0 );
		expect( result.total ).toBe( 0 );
	} );

	test( 'parses a list with null nullable fields', () => {
		const response = makeLogListResponse( [ makeLog404( { agent: null, ip: null, referrer: null } ) ], 1 );
		const result = Error404ListResponseSchema.parse( response );
		expect( result.items[ 0 ].agent ).toBeNull();
		expect( result.items[ 0 ].ip ).toBeNull();
	} );

	test( 'rejects an item with null id inside the list', () => {
		// This is the exact regression: grouped agent rows with NULL agent returned null id
		const response = makeLogListResponse( [ makeLog404( { id: null } ) ], 1 );
		expect( () => Error404ListResponseSchema.parse( response ) ).toThrow();
	} );

	test( 'accepts an item with empty string id (the fix)', () => {
		// After the fix, PHP returns '' instead of null for null-valued group keys
		const response = makeLogListResponse( [ makeLog404( { id: '' } ) ], 1 );
		expect( () => Error404ListResponseSchema.parse( response ) ).not.toThrow();
	} );
} );
