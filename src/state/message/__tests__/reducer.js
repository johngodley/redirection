/* eslint-disable no-console */
/**
 * Internal dependencies
 */

global.console = { error: jest.fn() };

import reducer from '../../message/reducer';
import { MESSAGE_CLEAR_ERRORS, MESSAGE_CLEAR_NOTICES } from '../../message/type';
import {
	REDIRECT_FAILED,
	REDIRECT_ITEM_SAVING,
	REDIRECT_ITEM_FAILED,
	REDIRECT_ITEM_SAVED,
} from '../../redirect/type';
import {
	GROUP_FAILED,
	GROUP_ITEM_FAILED,
	GROUP_ITEM_SAVED,
	GROUP_ITEM_SAVING,
} from '../../group/type';
import { LOG_FAILED } from '../../log/type';
import {
	SETTING_LOAD_FAILED,
	SETTING_SAVE_FAILED,
	SETTING_SAVED,
	SETTING_SAVING,
} from '../../settings/type';

const DEFAULT_STATE = {
	errors: [],
	notices: [],
	inProgress: 0,
	saving: [],
};

const DEFAULT_ERROR = {
	action: 'action',
	data: JSON.stringify( 'data' ),
	error: 'fail',
	response: 'response',
};

global.Redirectioni10n = {
	failedAction: 'action',
	failedData: 'data',
	failedResponse: 'response',
};

describe( 'groups reducer', () => {
	test( 'unknown action returns same state', () => {
		expect( reducer( DEFAULT_STATE, { type: 'something' } ) ).toEqual( DEFAULT_STATE );
	} );

	test( 'Failure is added to list of errors, and inProgress counter is decremented', () => {
		const actions = [ GROUP_FAILED, REDIRECT_ITEM_FAILED, GROUP_ITEM_FAILED, LOG_FAILED, SETTING_LOAD_FAILED, SETTING_SAVE_FAILED, REDIRECT_FAILED ];

		for ( let x = 0; x < actions.length; x++ ) {
			const state = reducer( { ... DEFAULT_STATE, inProgress: 2 }, { type: actions[ x ], error: DEFAULT_ERROR } );

			expect( state ).toEqual( { ... DEFAULT_STATE, errors: [ DEFAULT_ERROR ], inProgress: 1 } );
			expect( console.error ).toBeCalled();
		}

		console.error.mockClear();
	} );

	test( 'Saving action results in inProgress being incremented', () => {
		const actions = [ REDIRECT_ITEM_SAVING, SETTING_SAVING, GROUP_ITEM_SAVING ];

		for ( let x = 0; x < actions.length; x++ ) {
			const state = reducer( { ... DEFAULT_STATE, inProgress: 2 }, { type: actions[ x ] } );

			expect( state ).toEqual( { ... DEFAULT_STATE, inProgress: 3 } );
			expect( console.error ).not.toBeCalled();
		}
	} );

	test( 'Saved action results in inProgress being decremented and a notice logged', () => {
		const actions = [ REDIRECT_ITEM_SAVED, SETTING_SAVED, GROUP_ITEM_SAVED ];
		const notices = [ 'Redirection saved', 'Settings saved', 'Group saved', 'Module saved' ];

		for ( let x = 0; x < actions.length; x++ ) {
			const state = reducer( { ... DEFAULT_STATE, inProgress: 2 }, { type: actions[ x ] } );

			expect( state ).toEqual( { ... DEFAULT_STATE, inProgress: 1, notices: [ notices[ x ] ] } );
			expect( console.error ).not.toBeCalled();
		}
	} );

	test( 'MESSAGE_CLEAR_NOTICES clears all notices', () => {
		const state = reducer( { ... DEFAULT_STATE, notices: [ 1, 2 ] }, { type: MESSAGE_CLEAR_NOTICES } );

		expect( state ).toEqual( DEFAULT_STATE );
	} );

	test( 'MESSAGE_CLEAR_ERRORS clears all notices', () => {
		const state = reducer( { ... DEFAULT_STATE, errors: [ 1, 2 ] }, { type: MESSAGE_CLEAR_ERRORS } );

		expect( state ).toEqual( DEFAULT_STATE );
	} );
} );
