/* eslint-disable no-console */
/**
 * Internal dependencies
 */

global.console = { error: jest.fn() };

import reducer from 'state/settings/reducer';
import { getInitialSettings } from 'state/settings/initial';
import {
	SETTING_LOAD_START,
	SETTING_LOAD_SUCCESS,
	SETTING_LOAD_FAILED,
	SETTING_SAVING,
	SETTING_SAVED,
	SETTING_SAVE_FAILED,

	STATUS_IN_PROGRESS,
	STATUS_COMPLETE,
	STATUS_FAILED,
} from 'state/settings/type';

const DEFAULT_STATE = getInitialSettings();

describe( 'groups reducer', () => {
	test( 'unknown action returns same state', () => {
		expect( reducer( DEFAULT_STATE, { type: 'something' } ) ).toEqual( DEFAULT_STATE );
	} );

	test( 'SETTING_LOAD_START sets load progress', () => {
		const state = reducer( DEFAULT_STATE, { type: SETTING_LOAD_START } );

		expect( state ).toEqual( { ... DEFAULT_STATE, loadStatus: STATUS_IN_PROGRESS } );
	} );

	test( 'SETTING_LOAD_SUCCESS sets load progress, values, and groups', () => {
		const state = reducer( DEFAULT_STATE, { type: SETTING_LOAD_SUCCESS, values: 1, groups: 2 } );

		expect( state ).toEqual( { ... DEFAULT_STATE, loadStatus: STATUS_COMPLETE, values: 1, groups: 2 } );
	} );

	test( 'SETTING_LOAD_FAILED sets failed', () => {
		const state = reducer( DEFAULT_STATE, { type: SETTING_LOAD_FAILED, error: 'fail' } );

		expect( state ).toEqual( { ... DEFAULT_STATE, loadStatus: STATUS_FAILED, error: 'fail' } );
	} );

	test( 'SETTING_SAVING sets save progress', () => {
		const state = reducer( DEFAULT_STATE, { type: SETTING_SAVING } );

		expect( state ).toEqual( { ... DEFAULT_STATE, saveStatus: STATUS_IN_PROGRESS } );
	} );

	test( 'SETTING_SAVED sets save progress, values, and groups', () => {
		const state = reducer( DEFAULT_STATE, { type: SETTING_SAVED, values: 1, groups: 2 } );

		expect( state ).toEqual( { ... DEFAULT_STATE, saveStatus: STATUS_COMPLETE, values: 1, groups: 2 } );
	} );

	test( 'SETTING_SAVE_FAILED sets failed', () => {
		const state = reducer( DEFAULT_STATE, { type: SETTING_SAVE_FAILED, error: 'fail' } );

		expect( state ).toEqual( { ... DEFAULT_STATE, saveStatus: STATUS_FAILED, error: 'fail' } );
	} );
} );
