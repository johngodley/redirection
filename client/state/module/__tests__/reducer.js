/**
 * Internal dependencies
 */

import reducer from 'state/module/reducer';
import {
	MODULE_LOADING,
	MODULE_LOADED,
	MODULE_FAILED,
} from 'state/module/type';
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
	status: STATUS_IN_PROGRESS,
	total: 3,
	table: NEW_TABLE,
};
const MODULE_DATA = [
	{
		module_id: 1,
		name: 'wordpress',
	},
	{
		module_id: 2,
		name: 'apache',
	},
	{
		module_id: 3,
		name: 'nginx',
	},
];
const NEW_MODULE = {
	wordpress: {
		module_id: 1,
		name: 'wordpress',
		data: 1,
	}
};
const EXPECTED_MODULES = [
	{
		module_id: 1,
		name: 'wordpress',
	},
	{
		module_id: 2,
		name: 'apache',
	},
	{
		module_id: 3,
		name: 'nginx',
	},
];

describe( 'modules reducer', () => {
	test( 'unknown action returns same state', () => {
		expect( reducer( DEFAULT_STATE, { type: 'something' } ) ).toEqual( DEFAULT_STATE );
	} );

	test( 'MODULE_LOADING sets status in progress', () => {
		const state = reducer( DEFAULT_STATE, { type: MODULE_LOADING } );

		expect( state.table ).toEqual( NEW_TABLE );
		expect( state.status ).toEqual( STATUS_IN_PROGRESS );
	} );

	test( 'MODULE_FAILED sets status to failed', () => {
		const state = reducer( DEFAULT_STATE, { type: MODULE_FAILED } );

		expect( state.table ).toEqual( NEW_TABLE );
		expect( state.status ).toEqual( STATUS_FAILED );
	} );

	test( 'MODULE_LOADED sets status complete and updates rows on first time', () => {
		const state = reducer( DEFAULT_STATE, { type: MODULE_LOADED, rows: MODULE_DATA } );

		expect( state.table ).toEqual( NEW_TABLE );
		expect( state.status ).toEqual( STATUS_COMPLETE );
		expect( state.rows ).toEqual( EXPECTED_MODULES );
	} );

	test( 'MODULE_LOADED updates existing rows', () => {
		const state = reducer( Object.assign( {}, DEFAULT_STATE, { rows: EXPECTED_MODULES } ), { type: MODULE_LOADED, rows: NEW_MODULE } );
		const expected = EXPECTED_MODULES;

		expected[ 1 ].data = 1;

		expect( state.table ).toEqual( NEW_TABLE );
		expect( state.status ).toEqual( STATUS_COMPLETE );
		expect( state.rows ).toEqual( EXPECTED_MODULES );
	} );
} );
