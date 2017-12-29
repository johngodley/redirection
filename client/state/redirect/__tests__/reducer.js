/**
 * Internal dependencies
 */

import reducer from 'state/redirect/reducer';
import {
	REDIRECT_LOADED,
	REDIRECT_LOADING,
	REDIRECT_FAILED,
	REDIRECT_SET_SELECTED,
	REDIRECT_SET_ALL_SELECTED,
	REDIRECT_ITEM_SAVING,
	REDIRECT_ITEM_SAVED,
	REDIRECT_ITEM_FAILED,
} from 'state/redirect/type';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from 'state/settings/type';
import { setTable, setRows, setTotal, setItem, setSaving, removeSaving, restoreToOriginal } from 'lib/store';
import { setTableSelected, setTableAllSelected, clearSelected } from 'lib/table';

const NEW_TABLE = {
	orderby: 'name',
	direction: 'desc',
	page: 0,
	per_page: 25,
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
	saving: [],
};

jest.mock( 'lib/store' );
jest.mock( 'lib/table' );

const isCalled = ( mocker, first, second ) => expect( mocker.mock.calls[ 0 ][ 0 ] ).toBe( first ) && expect( mocker.mock.calls[ 0 ][ 1 ] ).toBe( second );

// Note that we just test that the lib/store functions are called, not what the lib/store functions do
describe( 'redirects reducer', () => {
	const STATE = { state: true, table: true };

	test( 'unknown action returns same state', () => {
		expect( reducer( DEFAULT_STATE, { type: 'something' } ) ).toEqual( DEFAULT_STATE );
	} );

	test( 'REDIRECT_LOADING', () => {
		const action = { type: REDIRECT_LOADING };
		const state = reducer( STATE, action );

		isCalled( setTable, STATE, action );
		expect( state.status ).toBe( STATUS_IN_PROGRESS );
		expect( state.saving ).toEqual( [] );
	} );

	test( 'REDIRECT_FAILED', () => {
		const action = { type: REDIRECT_FAILED };
		const state = reducer( STATE, action );

		expect( state.status ).toBe( STATUS_FAILED );
		expect( state.saving ).toEqual( [] );
	} );

	test( 'REDIRECT_LOADED', () => {
		const action = { type: REDIRECT_LOADED };
		const state = reducer( STATE, action );

		expect( state.status ).toBe( STATUS_COMPLETE );
		isCalled( setRows, STATE, action );
		isCalled( setTotal, STATE, action );
		expect( clearSelected.mock.calls[ 0 ][ 0 ] ).toBe( STATE.table );
	} );

	test( 'REDIRECT_SET_SELECTED', () => {
		const action = { type: REDIRECT_SET_SELECTED };

		reducer( STATE, action );

		isCalled( setTableSelected, STATE.table, STATE.rows );
	} );

	test( 'REDIRECT_SET_ALL_SELECTED', () => {
		const action = { type: REDIRECT_SET_ALL_SELECTED, onoff: true };

		reducer( STATE, action );

		isCalled( setTableAllSelected, STATE.table, STATE.rows );
		expect( setTableAllSelected.mock.calls[ 0 ][ 2 ] ).toBe( true );
	} );

	test( 'REDIRECT_ITEM_SAVING', () => {
		const action = { type: REDIRECT_ITEM_SAVING };

		reducer( STATE, action );

		isCalled( setTable, STATE, action );
		isCalled( setSaving, STATE, action );
		isCalled( setItem, STATE, action );
		expect( clearSelected.mock.calls[ 0 ][ 0 ] ).toBe( STATE.table );
	} );

	test( 'REDIRECT_ITEM_SAVED', () => {
		const action = { type: REDIRECT_ITEM_SAVED };

		reducer( STATE, action );

		isCalled( setRows, STATE, action );
		isCalled( setTotal, STATE, action );
		isCalled( removeSaving, STATE, action );
	} );

	test( 'REDIRECT_ITEM_FAILED', () => {
		const action = { type: REDIRECT_ITEM_FAILED };

		reducer( STATE, action );

		isCalled( removeSaving, STATE, action );
		isCalled( restoreToOriginal, STATE, action );
	} );
} );
