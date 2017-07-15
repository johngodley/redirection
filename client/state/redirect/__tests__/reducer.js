/**
 * Internal dependencies
 */

import reducer from 'state/group/reducer';
import {
	GROUP_LOADED,
	GROUP_LOADING,
	GROUP_FAILED,
	GROUP_SET_SELECTED,
	GROUP_SET_ALL_SELECTED,
	GROUP_ITEM_SAVING,
	GROUP_ITEM_SAVED,
	GROUP_ITEM_FAILED,
} from 'state/group/type';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from 'state/settings/type';

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
const DEFAULT_STATE = {
	rows: [],
	status: 1,
	total: 0,
	error: false,
	table: NEW_TABLE,
	saving: false,
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
const EXPECTED_ROWS = [
	{
		id: 1,
	},
	{
		id: 2,
		name: 'new',
	},
	{
		id: 3,
	}
];
global.Redirectioni10n = {
	per_page: 25,
};

jest.mock( 'lib/wordpress-url' );

describe( 'groups reducer', () => {
	test( 'unknown action returns same state', () => {
		expect( reducer( DEFAULT_STATE, { type: 'something' } ) ).toEqual( DEFAULT_STATE );
	} );

	test( 'GROUP_LOADING with no table data leaves table unchanged', () => {
		const state = reducer( DEFAULT_STATE, { type: GROUP_LOADING } );

		expect( state.table ).toEqual( NEW_TABLE );
	} );

	test( 'GROUP_LOADING sets status and table', () => {
		const state = reducer( DEFAULT_STATE, Object.assign( { type: GROUP_LOADING }, NEW_TABLE ) );

		expect( state.status ).toBe( STATUS_IN_PROGRESS );
		expect( state.table ).toEqual( NEW_TABLE );
	} );

	test( 'GROUP_FAILED sets status and error and ignores table', () => {
		const state = reducer( DEFAULT_STATE, Object.assign( { type: GROUP_FAILED }, NEW_TABLE, { error: 'failed' } ) );

		expect( state.status ).toBe( STATUS_FAILED );
		expect( state.table ).toEqual( NEW_TABLE );
		expect( state.error ).toEqual( 'failed' );
	} );

	test( 'GROUP_LOADED sets status and rows and ignores table', () => {
		const state = reducer( DEFAULT_STATE, Object.assign( { type: GROUP_LOADED, rows: DEFAULT_ROWS, total: 3 }, NEW_TABLE ) );

		expect( state.status ).toBe( STATUS_COMPLETE );
		expect( state.total ).toEqual( 3 );
		expect( state.rows ).toEqual( DEFAULT_ROWS );
		expect( state.table ).toEqual( NEW_TABLE );
	} );

	test( 'GROUP_SET_SELECTED sets selected in table', () => {
		const state = reducer( DEFAULT_STATE, Object.assign( { type: GROUP_SET_SELECTED, items: [ 1, 3 ] }, NEW_TABLE ) );

		expect( state.table ).toEqual( Object.assign( {}, NEW_TABLE, { selected: [ 1, 3 ] } ) );
		expect( state.status ).toBe( DEFAULT_STATE.status );
	} );

	test( 'GROUP_SET_ALL_SELECTED sets all selected in table', () => {
		const state = reducer( Object.assign( {}, DEFAULT_STATE, { rows: DEFAULT_ROWS } ), Object.assign( { type: GROUP_SET_ALL_SELECTED, onoff: true }, NEW_TABLE ) );

		expect( state.table ).toEqual( Object.assign( {}, NEW_TABLE, { selected: [ 1, 2, 3 ] } ) );
		expect( state.status ).toBe( DEFAULT_STATE.status );
	} );

	test( 'GROUP_ITEM_SAVING sets table and rows', () => {
		const state = reducer( Object.assign( {}, DEFAULT_STATE, { rows: DEFAULT_ROWS } ), Object.assign( { type: GROUP_ITEM_SAVING, group: { groupId: 2, name: 'new' }, NEW_TABLE } ) );

		expect( state.table ).toEqual( NEW_TABLE );
		expect( state.saving ).toBe( true );
		expect( state.rows ).toEqual( EXPECTED_ROWS );
	} );

	test( 'GROUP_ITEM_SAVED sets rows and total for new items', () => {
		const state = reducer( DEFAULT_STATE, { type: GROUP_ITEM_SAVED, total: 3, items: DEFAULT_ROWS } );

		expect( state.table ).toEqual( NEW_TABLE );
		expect( state.total ).toEqual( 3 );
		expect( state.saving ).toBe( false );
		expect( state.rows ).toEqual( DEFAULT_ROWS );
	} );

	test( 'GROUP_ITEM_SAVED sets rows and total for existing items', () => {
		const state = reducer( Object.assign( {}, DEFAULT_STATE, { rows: DEFAULT_ROWS } ), { type: GROUP_ITEM_SAVED, total: 3, id: 2, name: 'new' } );

		expect( state.table ).toEqual( NEW_TABLE );
		expect( state.total ).toEqual( 3 );
		expect( state.saving ).toBe( false );
		expect( state.rows ).toEqual( EXPECTED_ROWS );
	} );

	test( 'GROUP_ITEM_FAILED sets status', () => {
		const state = reducer( DEFAULT_STATE, { type: GROUP_ITEM_FAILED } );

		expect( state.saving ).toBe( false );
		expect( state.table ).toEqual( NEW_TABLE );
	} );
} );
