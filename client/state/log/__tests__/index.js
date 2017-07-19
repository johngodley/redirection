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
import { mergeWithTable, setTableSelected, setTableAllSelected } from 'lib/table';

global.Redirectioni10n = {};

const DEFAULT_STATE = getInitialLog();

jest.mock( 'lib/table' );

const isCalled = ( mocker, first, second ) => expect( mocker.mock.calls[ 0 ][ 0 ] ).toBe( first ) && expect( mocker.mock.calls[ 0 ][ 1 ] ).toBe( second );

describe( 'log reducer', () => {
	test( 'unknown action returns same state', () => {
		expect( reducer( DEFAULT_STATE, { type: 'something' } ) ).toEqual( DEFAULT_STATE );
	} );

	test( 'LOG_LOADING updates table, resets error, and sets logType and status', () => {
		const action = { type: LOG_LOADING, logType: 'cat', page: 5, something: 'else' };
		const state = reducer( DEFAULT_STATE, action );

		isCalled( mergeWithTable, DEFAULT_STATE.table, action );

		expect( state.status ).toEqual( STATUS_IN_PROGRESS );
		expect( state.error ).toEqual( false );
		expect( state.logType ).toEqual( 'cat' );
	} );

	test( 'LOG_LOADED sets rows, status, and total', () => {
		const state = reducer( DEFAULT_STATE, { type: LOG_LOADED, rows: [ 1, 2 ], total: 5 } );

		expect( state ).toEqual( { ... DEFAULT_STATE, status: STATUS_COMPLETE, total: 5, rows: [ 1, 2 ] } );
	} );

	test( 'LOG_FAILED sets status and error', () => {
		const state = reducer( DEFAULT_STATE, { type: LOG_FAILED, error: 'yes' } );

		expect( state ).toEqual( { ... DEFAULT_STATE, status: STATUS_FAILED, error: 'yes' } );
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
