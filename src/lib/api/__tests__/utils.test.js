import { getTableResetOnDelete, cleanApiParams, getShowFilter } from '../utils';

// ---------------------------------------------------------------------------
// getTableResetOnDelete
//
// Controls whether the table page resets to 0 after a bulk delete.
// Only resets when params.global === true (select-all action).
// Single-item and non-global bulk deletes stay on the current page (#4151).
// ---------------------------------------------------------------------------

describe( 'getTableResetOnDelete', () => {
	describe( 'global delete (select-all)', () => {
		test( 'resets page to 0 when params.global is true', () => {
			const result = getTableResetOnDelete( { global: true } );
			expect( result.page ).toBe( 0 );
		} );

		test( 'clears selected', () => {
			const result = getTableResetOnDelete( { global: true } );
			expect( result.selected ).toEqual( [] );
		} );

		test( 'sets selectAll to false', () => {
			const result = getTableResetOnDelete( { global: true } );
			expect( result.selectAll ).toBe( false );
		} );
	} );

	describe( 'single-item or non-global delete', () => {
		test( 'does not include page when params.global is false', () => {
			const result = getTableResetOnDelete( { global: false } );
			expect( result ).not.toHaveProperty( 'page' );
		} );

		test( 'does not include page when params.global is absent', () => {
			const result = getTableResetOnDelete( {} );
			expect( result ).not.toHaveProperty( 'page' );
		} );

		test( 'does not include page when params is undefined', () => {
			const result = getTableResetOnDelete( undefined );
			expect( result ).not.toHaveProperty( 'page' );
		} );

		test( 'still clears selected', () => {
			const result = getTableResetOnDelete( { global: false } );
			expect( result.selected ).toEqual( [] );
		} );

		test( 'still sets selectAll to false', () => {
			const result = getTableResetOnDelete( undefined );
			expect( result.selectAll ).toBe( false );
		} );
	} );

	describe( 'edge cases', () => {
		test( 'does not include page when global is a truthy non-boolean', () => {
			// Must be strict true — string 'true' or 1 should not trigger page reset
			const result = getTableResetOnDelete( { global: 'true' } );
			expect( result ).not.toHaveProperty( 'page' );
		} );

		test( 'does not include page when global is 1', () => {
			const result = getTableResetOnDelete( { global: 1 } );
			expect( result ).not.toHaveProperty( 'page' );
		} );
	} );
} );

// ---------------------------------------------------------------------------
// getShowFilter
//
// Maps a groupBy value to the correct filterBy key for "Show All" actions.
// Used in both log and 404 row-actions when the user clicks "Show All".
// ---------------------------------------------------------------------------

describe( 'getShowFilter', () => {
	describe( 'groupBy=ip', () => {
		test( 'returns { ip: id }', () => {
			expect( getShowFilter( 'ip', '10.0.0.1' ) ).toEqual( { ip: '10.0.0.1' } );
		} );

		test( 'works with any string id', () => {
			expect( getShowFilter( 'ip', '::1' ) ).toEqual( { ip: '::1' } );
		} );
	} );

	describe( 'groupBy=agent', () => {
		test( 'returns { agent: id }', () => {
			expect( getShowFilter( 'agent', 'Mozilla/5.0' ) ).toEqual( { agent: 'Mozilla/5.0' } );
		} );

		test( 'handles empty string agent (null agent group)', () => {
			// PHP returns '' when agent IS NULL and groupBy=agent
			expect( getShowFilter( 'agent', '' ) ).toEqual( { agent: '' } );
		} );
	} );

	describe( 'groupBy=url (default)', () => {
		test( 'returns { url-exact: id } for url groupBy', () => {
			expect( getShowFilter( 'url', '/some-page' ) ).toEqual( { 'url-exact': '/some-page' } );
		} );

		test( 'returns { url-exact: id } for empty groupBy (ungrouped)', () => {
			// When not grouped, still falls through to url-exact
			expect( getShowFilter( '', '/some-page' ) ).toEqual( { 'url-exact': '/some-page' } );
		} );

		test( 'returns { url-exact: id } for any unknown groupBy', () => {
			expect( getShowFilter( 'unknown', '/foo' ) ).toEqual( { 'url-exact': '/foo' } );
		} );
	} );
} );

// ---------------------------------------------------------------------------
// cleanApiParams — existing utility, regression coverage
// ---------------------------------------------------------------------------

describe( 'cleanApiParams', () => {
	test( 'passes through normal values', () => {
		expect( cleanApiParams( { page: 1, orderby: 'url' } ) ).toEqual( { page: 1, orderby: 'url' } );
	} );

	test( 'strips empty filterBy object', () => {
		expect( cleanApiParams( { filterBy: {} } ) ).toEqual( {} );
	} );

	test( 'keeps filterBy when it has values', () => {
		expect( cleanApiParams( { filterBy: { url: 'test' } } ) ).toEqual( { filterBy: { url: 'test' } } );
	} );

	test( 'strips empty arrays', () => {
		expect( cleanApiParams( { selected: [] } ) ).toEqual( {} );
	} );

	test( 'strips empty strings', () => {
		expect( cleanApiParams( { groupBy: '' } ) ).toEqual( {} );
	} );

	test( 'strips null values', () => {
		expect( cleanApiParams( { thing: null } ) ).toEqual( {} );
	} );

	test( 'strips undefined values', () => {
		expect( cleanApiParams( { thing: undefined } ) ).toEqual( {} );
	} );

	test( 'strips UI-only fields', () => {
		const result = cleanApiParams( {
			page: 1,
			selected: [ 1, 2 ],
			selectAll: true,
			displayType: 'standard',
			displaySelected: [ 'url' ],
		} );
		expect( result ).toEqual( { page: 1 } );
	} );
} );
