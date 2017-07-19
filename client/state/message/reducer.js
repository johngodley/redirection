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
	REDIRECT_ITEM_SAVING,
	REDIRECT_ITEM_FAILED,
	REDIRECT_ITEM_SAVED,
} from 'state/redirect/type';
import {
	GROUP_FAILED,
	GROUP_ITEM_FAILED,
	GROUP_ITEM_SAVED,
	GROUP_ITEM_SAVING,
} from 'state/group/type';
import { LOG_FAILED } from 'state/log/type';
import {
	MODULE_FAILED,
	MODULE_SAVING,
	MODULE_SAVED,
} from 'state/module/type';
import {
	SETTING_LOAD_FAILED,
	SETTING_SAVE_FAILED,
	SETTING_SAVED,
	SETTING_SAVING,
} from 'state/settings/type';

const parseError = error => ( {
	action: Redirectioni10n.failedAction,
	data: JSON.stringify( Redirectioni10n.failedData ? Redirectioni10n.failedData : '' ),
	error: error.message ? error.message : error,
	response: Redirectioni10n.failedResponse,
} );
const addError = ( existing, error ) => existing.slice( 0 ).concat( [ parseError( error ) ] );
const addNotice = ( existing, notice ) => existing.slice( 0 ).concat( [ notice ] );
const reduceProgress = state => Math.max( 0, state.inProgress - 1 );

const NOTICES = {
	REDIRECT_ITEM_SAVED: __( 'Redirection saved' ),
	SETTING_SAVED: __( 'Settings saved' ),
	GROUP_ITEM_SAVED: __( 'Group saved' ),
	MODULE_SAVED: __( 'Module saved' ),
};

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
			return { ... state, errors: addError( state.errors, action.error ), inProgress: reduceProgress( state ) };

		case REDIRECT_ITEM_SAVING:
		case MODULE_SAVING:
		case SETTING_SAVING:
		case GROUP_ITEM_SAVING:
			return { ... state, inProgress: state.inProgress + 1 };

		case REDIRECT_ITEM_SAVED:
		case SETTING_SAVED:
		case GROUP_ITEM_SAVED:
		case MODULE_SAVED:
			return { ... state, notices: addNotice( state.notices, NOTICES[ action.type ] ), inProgress: reduceProgress( state ) };

		case MESSAGE_CLEAR_NOTICES:
			return { ... state, notices: [] };

		case MESSAGE_CLEAR_ERRORS:
			return { ... state, errors: [] };
	}

	return state;
}
