/**
 * Internal dependencies
 */

import reducer from 'state/module/reducer';
import {
	MODULE_LOADING,
	MODULE_LOADED,
	MODULE_ITEM_SAVING,
	MODULE_ITEM_SAVED,
	MODULE_ITEM_FAILED,
	MODULE_FAILED,
} from 'state/module/type';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from 'state/settings/type';
import { setTable, setRows, setTotal, setItem, setSaving, removeSaving, restoreToOriginal } from 'lib/store';

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
	status: 0,
	total: 0,
	error: false,
	table: NEW_TABLE,
	saving: [],
};

jest.mock( 'lib/store' );

const isCalled = ( mocker, first, second ) => expect( mocker.mock.calls[ 0 ][ 0 ] ).toBe( first ) && expect( mocker.mock.calls[ 0 ][ 1 ] ).toBe( second );

describe( 'modules reducer', () => {
	test( 'unknown action returns same state', () => {
		expect( reducer( DEFAULT_STATE, { type: 'something' } ) ).toEqual( DEFAULT_STATE );
	} );

	test( 'MODULE_LOADING', () => {
		const action = { type: MODULE_LOADING };
		const state = reducer( DEFAULT_STATE, action );

		isCalled( setTable, DEFAULT_STATE, action );
		expect( state.status ).toBe( STATUS_IN_PROGRESS );
		expect( state.saving ).toEqual( [] );
	} );

	test( 'MODULE_FAILED', () => {
		const action = { type: MODULE_FAILED };
		const state = reducer( DEFAULT_STATE, action );

		expect( state.status ).toBe( STATUS_FAILED );
		expect( state.saving ).toEqual( [] );
	} );

	test( 'MODULE_LOADED', () => {
		const action = { type: MODULE_LOADED };
		const state = reducer( DEFAULT_STATE, action );

		expect( state.status ).toBe( STATUS_COMPLETE );
		isCalled( setRows, DEFAULT_STATE, action );
		isCalled( setTotal, DEFAULT_STATE, action );
	} );

	test( 'MODULE_ITEM_SAVING', () => {
		const action = { type: MODULE_ITEM_SAVING };

		reducer( DEFAULT_STATE, action );

		isCalled( setTable, DEFAULT_STATE, action );
		isCalled( setSaving, DEFAULT_STATE, action );
		isCalled( setItem, DEFAULT_STATE, action );
	} );

	test( 'MODULE_ITEM_SAVED', () => {
		const action = { type: MODULE_ITEM_SAVED };

		reducer( DEFAULT_STATE, action );

		isCalled( setRows, DEFAULT_STATE, action );
		isCalled( setTotal, DEFAULT_STATE, action );
		isCalled( removeSaving, DEFAULT_STATE, action );
	} );

	test( 'MODULE_ITEM_FAILED', () => {
		const action = { type: MODULE_ITEM_FAILED };

		reducer( DEFAULT_STATE, action );

		isCalled( removeSaving, DEFAULT_STATE, action );
		isCalled( restoreToOriginal, DEFAULT_STATE, action );
	} );
} );
