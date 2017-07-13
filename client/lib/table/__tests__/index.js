/**
 * Internal dependencies
 */

import {
	mergeWithTable,
	setTableParams,
	setTableSelected,
	setTableAllSelected,
} from 'lib/table';
import { setPageUrl } from 'lib/wordpress-url';

const NEW_TABLE = {
	orderBy: 'name',
	direction: 'desc',
	page: 0,
	perPage: 25,
	selected: [],
	filterBy: '',
	filter: '',
	error: false,
};
const DEFAULT_ROWS = [
	{
		id: 1,
	},
	{
		id: 2,
	},
	{
		id: 3,
	}
];
global.Redirectioni10n = {
	per_page: 25,
};

jest.mock( 'lib/wordpress-url' );

describe( 'tables', () => {
	test( 'mergeWithTable leaves state alone with no params', () => {
		const state = mergeWithTable( NEW_TABLE, {} );

		expect( state ).toEqual( NEW_TABLE );
	} );

	test( 'mergeWithTable with param changes just that param', () => {
		const state = mergeWithTable( NEW_TABLE, { perPage: 50 } );

		expect( state ).toEqual( Object.assign( {}, NEW_TABLE, { perPage: 50 } ) );
	} );

	test( 'mergeWithTable with invalid param leaves state unchanged', () => {
		const state = mergeWithTable( NEW_TABLE, { cats: 50 } );

		expect( state ).toEqual( NEW_TABLE );
	} );

	test( 'setTableParams with param changes just that param and updates URL', () => {
		const state = setTableParams( NEW_TABLE, { perPage: 50 }, 'name' );

		expect( state ).toEqual( Object.assign( {}, NEW_TABLE, { perPage: 50 } ) );
		expect( setPageUrl.mock.calls.length ).toBe( 1 );
		expect( setPageUrl.mock.calls[ 0 ][ 0 ] ).toEqual( {
			orderBy: 'name',
			direction: 'desc',
			offset: 0,
			perPage: 50,
			filterBy: '',
			filter: '',
		} );
		expect( setPageUrl.mock.calls[ 0 ][ 1 ] ).toEqual( {
			orderBy: 'name',
			direction: 'desc',
			offset: 0,
			perPage: 25,
			filter: '',
			filterBy: '',
		} );
	} );

	test( 'setTableSelected sets no items if passed nothing', () => {
		const table = setTableSelected( NEW_TABLE, [] );

		expect( table.selected ).toEqual( [] );
	} );

	test( 'setTableSelected sets only the items passed', () => {
		const table = setTableSelected( NEW_TABLE, [ 1, 2 ] );

		expect( table.selected ).toEqual( [ 1, 2 ] );
	} );

	test( 'setTableAllSelected sets all items', () => {
		const table = setTableAllSelected( NEW_TABLE, DEFAULT_ROWS, true );

		expect( table.selected ).toEqual( [ 1, 2, 3 ] );
	} );

	test( 'setTableAllSelected clears all items', () => {
		const table = setTableAllSelected( Object.assign( {}, NEW_TABLE, { selected: [ 1, 2, 3 ] } ), false );

		expect( table.selected ).toEqual( [] );
	} );
} );
