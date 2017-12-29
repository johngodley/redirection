/**
 * Internal dependencies
 */

import {
	mergeWithTable,
	setTableSelected,
	setTableAllSelected,
	clearSelected,
	removeDefaults,
	getDefaultTable,
} from 'lib/table';
import { getPageUrl } from 'lib/wordpress-url';

const NEW_TABLE = {
	orderby: 'name',
	direction: 'desc',
	page: 0,
	per_page: 25,
	selected: [],
	filterBy: '',
	filter: '',
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
		const state = mergeWithTable( NEW_TABLE, { per_page: 50 } );

		expect( state ).toEqual( Object.assign( {}, NEW_TABLE, { per_page: 50 } ) );
	} );

	test( 'mergeWithTable with invalid param leaves state unchanged', () => {
		const state = mergeWithTable( NEW_TABLE, { cats: 50 } );

		expect( state ).toEqual( NEW_TABLE );
	} );

	test( 'mergeWithTable with param changes just that param', () => {
		const state = mergeWithTable( NEW_TABLE, { per_page: 50 }, 'name' );

		expect( state ).toEqual( Object.assign( {}, NEW_TABLE, { per_page: 50 } ) );
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

	test( 'clearSelected resets the selected items', () => {
		const table = clearSelected( { dummy: true, selected: [ 1, 2, 3, 4 ] } );

		expect( table.selected ).toEqual( [] );
		expect( table.dummy ).toEqual( true );
	} );

	test( 'removeDefaults does nothing to an object with no matching properties', () => {
		const table = removeDefaults( { dummy: true } );

		expect( table ).toEqual( { dummy: true } );
	} );

	test( 'removeDefaults removes the default table properties', () => {
		const table = removeDefaults( { direction: 'desc', page: 0, orderby: 'test', per_page: 25, dummy: true }, 'test' );

		expect( table ).toEqual( { dummy: true } );
	} );

	test( 'getDefaultTable doesnt allow overriding when not on query page', () => {
		getPageUrl.mockReturnValueOnce( { sub: 'notpage', orderby: 'other', direction: 'asc', offset: 5, filterby: 'filter', filter: 'monkey' } );

		const table = getDefaultTable( [], [], 'name', 'page' );

		expect( table ).toEqual( NEW_TABLE );
	} );

	test( 'getDefaultTable returns default with query override when on page', () => {
		getPageUrl.mockReturnValueOnce( { sub: 'page', orderby: 'other', direction: 'asc', offset: 5, filterby: 'filter', filter: 'monkey' } );

		const table = getDefaultTable( [ 'other' ], [ 'filter' ], 'name', [ 'page' ] );

		expect( table ).toEqual( Object.assign( {}, NEW_TABLE, { orderby: 'other', direction: 'asc', page: 5, filterBy: 'filter', filter: 'monkey' } ) );
	} );
} );
