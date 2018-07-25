/**
 * Internal dependencies
 */
import {
	ERROR_LOADED,
	ERROR_LOADING,
	ERROR_FAILED,
	ERROR_SET_SELECTED,
	ERROR_SET_ALL_SELECTED,
} from 'state/error/type';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from 'state/settings/type';
import reducer from 'state/error/reducer';
import { getInitialError } from 'state/error/initial';
import { setTableSelected, setTableAllSelected, clearSelected } from 'lib/table';
import { setTable, setRows, setTotal } from 'lib/store';

global.Redirectioni10n = {};

const DEFAULT_STATE = getInitialError();

jest.mock( 'lib/table' );
jest.mock( 'lib/store' );

const isCalled = ( mocker, first, second ) => expect( mocker.mock.calls[ 0 ][ 0 ] ).toBe( first ) && expect( mocker.mock.calls[ 0 ][ 1 ] ).toBe( second );

describe( 'log reducer', () => {
	test( 'unknown action returns same state', () => {
		expect( reducer( DEFAULT_STATE, { type: 'something' } ) ).toEqual( DEFAULT_STATE );
	} );

	test( 'ERROR_LOADING', () => {
		const action = { type: ERROR_LOADING };
		const state = reducer( DEFAULT_STATE, action );

		isCalled( setTable, DEFAULT_STATE, action );
		expect( state.status ).toBe( STATUS_IN_PROGRESS );
		expect( state.saving ).toEqual( [] );
	} );

	test( 'ERROR_LOADED', () => {
		const action = { type: ERROR_LOADED };
		const state = reducer( DEFAULT_STATE, action );

		expect( state.status ).toBe( STATUS_COMPLETE );
		isCalled( setRows, DEFAULT_STATE, action );
		isCalled( setTotal, DEFAULT_STATE, action );
		expect( clearSelected.mock.calls[ 0 ][ 0 ] ).toBe( DEFAULT_STATE.table );
	} );

	test( 'ERROR_FAILED', () => {
		const action = { type: ERROR_FAILED };
		const state = reducer( DEFAULT_STATE, action );

		expect( state.status ).toBe( STATUS_FAILED );
		expect( state.saving ).toEqual( [] );
	} );

	test( 'ERROR_SET_SELECTED', () => {
		const action = { type: ERROR_SET_SELECTED };

		reducer( DEFAULT_STATE, action );

		isCalled( setTableSelected, DEFAULT_STATE.table, DEFAULT_STATE.rows );
	} );

	test( 'ERROR_SET_ALL_SELECTED', () => {
		const action = { type: ERROR_SET_ALL_SELECTED, onoff: true };

		reducer( DEFAULT_STATE, action );

		isCalled( setTableAllSelected, DEFAULT_STATE.table, DEFAULT_STATE.rows );
		expect( setTableAllSelected.mock.calls[ 0 ][ 2 ] ).toBe( true );
	} );
} );
