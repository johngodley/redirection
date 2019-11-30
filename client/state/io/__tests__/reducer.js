/**
 * Internal dependencies
 */

import reducer from 'state/io/reducer';
import {
	IO_EXPORTED,
	IO_EXPORTING,
	IO_IMPORTING,
	IO_IMPORTED,
	IO_FAILED,
	IO_CLEAR,
	IO_ADD_FILE,
} from 'state/io/type';
import { getInitialIO } from 'state/io/initial';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from 'state/settings/type';

global.Redirectioni10n = {};
const DEFAULT_STATE = getInitialIO();

jest.mock( 'lib/store' );

describe( 'io reducer', () => {
	test( 'unknown action returns same state', () => {
		expect( reducer( DEFAULT_STATE, { type: 'something' } ) ).toEqual( DEFAULT_STATE );
	} );

	test( 'IO_EXPORTING sets export status', () => {
		const state = reducer( DEFAULT_STATE, { type: IO_EXPORTING } );

		expect( state ).toEqual( { ... DEFAULT_STATE, exportStatus: STATUS_IN_PROGRESS } );
	} );

	test( 'IO_EXPORTED sets export status and data', () => {
		const state = reducer( DEFAULT_STATE, { type: IO_EXPORTED, data: 'data' } );

		expect( state ).toEqual( { ... DEFAULT_STATE, exportStatus: STATUS_COMPLETE, exportData: 'data' } );
	} );

	test( 'IO_ADD_FILE sets file', () => {
		const state = reducer( DEFAULT_STATE, { type: IO_ADD_FILE, file: 'file' } );

		expect( state ).toEqual( { ... DEFAULT_STATE, file: 'file' } );
	} );

	test( 'IO_CLEAR clears file, import, and export data', () => {
		const state = reducer( { ... DEFAULT_STATE, file: 1, lastImport: 2, exportData: 3 }, { type: IO_CLEAR } );

		expect( state ).toEqual( DEFAULT_STATE );
	} );

	test( 'IO_FAILED clears file, import, and export data and sets fail status', () => {
		const state = reducer( { ... DEFAULT_STATE, file: 1, lastImport: 2, exportData: 3 }, { type: IO_FAILED } );

		expect( state ).toEqual( { ... DEFAULT_STATE, exportStatus: STATUS_FAILED, importingStatus: STATUS_FAILED } );
	} );

	test( 'IO_IMPORTING sets status and file, and clears last import', () => {
		const state = reducer( { ... DEFAULT_STATE, lastImport: 2 }, { type: IO_IMPORTING, file: 'file' } );

		expect( state ).toEqual( { ... DEFAULT_STATE, importingStatus: STATUS_IN_PROGRESS, file: 'file' } );
	} );

	test( 'IO_IMPORTED sets status and file and last import', () => {
		const state = reducer( { ... DEFAULT_STATE, file: 'file' }, { type: IO_IMPORTED, total: 5 } );

		expect( state ).toEqual( { ... DEFAULT_STATE, lastImport: 5, importingStatus: STATUS_COMPLETE, file: false } );
	} );
} );
