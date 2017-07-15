/* global Redirectioni10n */
/**
 * External dependencies
 */

import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */
import { MESSAGE_CLEAR_ERRORS, MESSAGE_CLEAR_NOTICES } from './type';
import {
	REDIRECT_FAILED,
	REDIRECT_ITEM_FAILED,
	REDIRECT_ITEM_SAVED,
} from 'state/redirect/type';
import {
	GROUP_FAILED,
	GROUP_ITEM_FAILED,
	GROUP_ITEM_SAVED,
} from 'state/group/type';
import { LOG_FAILED } from 'state/log/type';
import { MODULE_FAILED } from 'state/module/type';
import {
	SETTING_LOAD_FAILED,
	SETTING_SAVE_FAILED,
	SETTING_SAVED,
} from 'state/settings/type';

const parseError = error => ( {
	action: Redirectioni10n.failedAction,
	data: JSON.stringify( Redirectioni10n.failedData ? Redirectioni10n.failedData : '' ),
	error: error.message ? error.message : error
} );
const addError = ( existing, error ) => existing.slice( 0 ).concat( [ parseError( error ) ] );
const addNotice = ( existing, notice ) => existing.slice( 0 ).concat( [ notice ] );

export default function messages( state = {}, action ) {
	switch ( action.type ) {
		case GROUP_FAILED:
		case REDIRECT_ITEM_FAILED:
		case GROUP_ITEM_FAILED:
		case LOG_FAILED:
		case MODULE_FAILED:
		case SETTING_LOAD_FAILED:
		case SETTING_SAVE_FAILED:
		case REDIRECT_FAILED:
			/* eslint-disable */
			console.error( action.error );
			return { ... state, errors: addError( state.errors, action.error ) };

		case REDIRECT_ITEM_SAVED:
			return { ... state, notices: addNotice( state.notices, __( 'Redirection saved' ) ) };

		case SETTING_SAVED:
			return { ... state, notices: addNotice( state.notices, __( 'Settings saved' ) ) };

		case GROUP_ITEM_SAVED:
			return { ... state, notices: addNotice( state.notices, __( 'Group saved' ) ) };

		case MESSAGE_CLEAR_NOTICES:
			return { ... state, notices: [] };

		case MESSAGE_CLEAR_ERRORS:
			return { ... state, errors: [] };
	}

	return state;
}
