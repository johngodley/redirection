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
import {
	LOG_FAILED,
	LOG_ITEM_SAVING,
	LOG_ITEM_SAVED,
	LOG_ITEM_FAILED,
} from 'state/log/type';
import {
	ERROR_FAILED,
	ERROR_ITEM_SAVING,
	ERROR_ITEM_SAVED,
	ERROR_ITEM_FAILED,
} from 'state/error/type';
import {
	SETTING_LOAD_FAILED,
	SETTING_SAVE_FAILED,
	SETTING_SAVED,
	SETTING_SAVING,
	SETTING_DATABASE_FAILED,
} from 'state/settings/type';
import { IO_FAILED } from 'state/io/type';

const addErrors = ( existing, error ) => existing.slice( 0 ).concat( [ error ] );
const addNotice = ( existing, notice ) => existing.slice( 0 ).concat( [ notice ] );
const reduceProgress = state => Math.max( 0, state.inProgress - 1 );

const NOTICES = {
	REDIRECT_ITEM_SAVED: __( 'Redirection saved' ),
	LOG_ITEM_SAVED: __( 'Log deleted' ),
	SETTING_SAVED: __( 'Settings saved' ),
	GROUP_ITEM_SAVED: __( 'Group saved' ),
	ERROR_ITEM_SAVED: __( '404 deleted' ),
};

export default function messages( state = {}, action ) {
	switch ( action.type ) {
		case IO_FAILED:
		case GROUP_FAILED:
		case REDIRECT_ITEM_FAILED:
		case GROUP_ITEM_FAILED:
		case LOG_FAILED:
		case LOG_ITEM_FAILED:
		case SETTING_LOAD_FAILED:
		case SETTING_SAVE_FAILED:
		case ERROR_ITEM_FAILED:
		case ERROR_FAILED:
		case SETTING_DATABASE_FAILED:
		case REDIRECT_FAILED:
			/* eslint-disable */
			const errors = addErrors( state.errors, action.error );
			console.error( action.error.message );
			/* eslint-disable */

			return { ... state, errors, inProgress: reduceProgress( state ) };

		case LOG_ITEM_SAVING:
		case REDIRECT_ITEM_SAVING:
		case SETTING_SAVING:
		case ERROR_ITEM_SAVING:
		case GROUP_ITEM_SAVING:
			return { ... state, inProgress: state.inProgress + 1 };

		case LOG_ITEM_SAVED:
		case REDIRECT_ITEM_SAVED:
		case SETTING_SAVED:
		case GROUP_ITEM_SAVED:
		case ERROR_ITEM_SAVED:
			return { ... state, notices: addNotice( state.notices, NOTICES[ action.type ] ), inProgress: reduceProgress( state ) };

		case MESSAGE_CLEAR_NOTICES:
			return { ... state, notices: [] };

		case MESSAGE_CLEAR_ERRORS:
			return { ... state, errors: [] };
	}

	return state;
}
