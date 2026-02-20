import { mergeWithTable, setTableSelected, clearSelected, removeDefaults, getDefaultTable } from 'lib/table';
import { getPageUrl } from 'lib/wordpress-url';

const NEW_TABLE = {
	orderby: 'name',
	groupBy: '',
	direction: 'desc',
	page: 0,
	per_page: 25,
	selected: [],
	filterBy: {},
	displaySelected: [],
	displayType: '',
	selectAll: false,
};

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

	test( 'clearSelected resets the selected items', () => {
		const table = clearSelected( { dummy: true, selected: [ 1, 2, 3, 4 ] } );

		expect( table.selected ).toEqual( [] );
		expect( table.dummy ).toEqual( true );
	} );

	test( 'clearSelected also clears selectAll', () => {
		const table = clearSelected( { selected: [ 1, 2 ], selectAll: true } );

		expect( table.selected ).toEqual( [] );
		expect( table.selectAll ).toBe( false );
	} );

	test( 'setTableSelected with true selects all rows and preserves selectAll param', () => {
		const rows = [ { id: 1 }, { id: 2 }, { id: 3 } ];
		const table = setTableSelected( NEW_TABLE, true, true, rows );

		expect( table.selected ).toEqual( [ 1, 2, 3 ] );
		expect( table.selectAll ).toBe( true );
	} );

	test( 'setTableSelected with false clears selection and sets selectAll to false', () => {
		const tableWithAll = { ...NEW_TABLE, selected: [ 1, 2, 3 ], selectAll: true };
		const table = setTableSelected( tableWithAll, false, false, [] );

		expect( table.selected ).toEqual( [] );
		expect( table.selectAll ).toBe( false );
	} );

	test( 'setTableSelected toggling a single item clears selectAll', () => {
		// Simulates unchecking one row after "Select All" was clicked
		const tableWithAll = { ...NEW_TABLE, selected: [ 1, 2, 3 ], selectAll: true };
		const table = setTableSelected( tableWithAll, [ 2 ], false, [] );

		expect( table.selected ).not.toContain( 2 );
		expect( table.selectAll ).toBe( false );
	} );

	test( 'removeDefaults does nothing to an object with no matching properties', () => {
		const table = removeDefaults( { dummy: true } );

		expect( table ).toEqual( { dummy: true } );
	} );

	test( 'removeDefaults removes the default table properties', () => {
		const table = removeDefaults(
			{ direction: 'desc', page: 0, orderby: 'test', per_page: 25, dummy: true },
			'test'
		);

		expect( table ).toEqual( { dummy: true } );
	} );

	test( 'getDefaultTable returns default with query override when on page', () => {
		getPageUrl.mockReturnValueOnce( { sub: 'page', orderby: 'other', direction: 'asc', offset: 5 } );

		const table = getDefaultTable( [ 'other' ], [ 'filter' ], [], 'name', [ 'page' ] );

		expect( table ).toEqual(
			Object.assign( {}, NEW_TABLE, { orderby: 'other', direction: 'asc', page: 5, displayType: 'standard' } )
		);
	} );
} );
