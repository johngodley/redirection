/**
 * Internal dependencies
 */
import {
	LOG_LOADED,
	LOG_LOADING,
	LOG_FAILED,
	LOG_SET_SELECTED,
	LOG_SET_ALL_SELECTED,
} from 'state/log/type';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from 'state/settings/type';
import reducer from 'state/log/reducer';
import { getInitialLog } from 'state/log/initial';
import { setTableSelected, setTableAllSelected, clearSelected } from 'lib/table';
import { setTable, setRows, setTotal } from 'lib/store';

global.Redirectioni10n = {};

const DEFAULT_STATE = getInitialLog();

jest.mock( 'lib/table' );
jest.mock( 'lib/store' );

const isCalled = ( mocker, first, second ) => expect( mocker.mock.calls[ 0 ][ 0 ] ).toBe( first ) && expect( mocker.mock.calls[ 0 ][ 1 ] ).toBe( second );

describe( 'log reducer', () => {
	test( 'unknown action returns same state', () => {
		expect( reducer( DEFAULT_STATE, { type: 'something' } ) ).toEqual( DEFAULT_STATE );
	} );

	test( 'LOG_LOADING', () => {
		const action = { type: LOG_LOADING };
		const state = reducer( DEFAULT_STATE, action );

		isCalled( setTable, DEFAULT_STATE, action );
		expect( state.status ).toBe( STATUS_IN_PROGRESS );
		expect( state.saving ).toEqual( [] );
	} );

	test( 'LOG_LOADED', () => {
		const action = { type: LOG_LOADED };
		const state = reducer( DEFAULT_STATE, action );

		expect( state.status ).toBe( STATUS_COMPLETE );
		isCalled( setRows, DEFAULT_STATE, action );
		isCalled( setTotal, DEFAULT_STATE, action );
		expect( clearSelected.mock.calls[ 0 ][ 0 ] ).toBe( DEFAULT_STATE.table );
	} );

	test( 'LOG_FAILED', () => {
		const action = { type: LOG_FAILED };
		const state = reducer( DEFAULT_STATE, action );

		expect( state.status ).toBe( STATUS_FAILED );
		expect( state.saving ).toEqual( [] );
	} );

	test( 'LOG_SET_SELECTED', () => {
		const action = { type: LOG_SET_SELECTED };

		reducer( DEFAULT_STATE, action );

		isCalled( setTableSelected, DEFAULT_STATE.table, DEFAULT_STATE.rows );
	} );

	test( 'LOG_SET_ALL_SELECTED', () => {
		const action = { type: LOG_SET_ALL_SELECTED, onoff: true };

		reducer( DEFAULT_STATE, action );

		isCalled( setTableAllSelected, DEFAULT_STATE.table, DEFAULT_STATE.rows );
		expect( setTableAllSelected.mock.calls[ 0 ][ 2 ] ).toBe( true );
	} );
} );
