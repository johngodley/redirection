/**
 * Internal dependencies
 */

import {
	SETTING_LOAD_START,
	SETTING_LOAD_SUCCESS,
	SETTING_LOAD_FAILED,
	SETTING_SAVING,
	SETTING_SAVED,
	SETTING_SAVE_FAILED,
	SETTING_LOAD_STATUS,
	SETTING_DATABASE_START,
	SETTING_DATABASE_SUCCESS,
	SETTING_DATABASE_COMPLETE,
	SETTING_DATABASE_FAILED,
	SETTING_DATABASE_FINISH,
	SETTING_DATABASE_SHOW,

	STATUS_IN_PROGRESS,
	STATUS_COMPLETE,
	STATUS_FAILED,
} from './type';

export default function settings( state = {}, action ) {
	switch ( action.type ) {
		case SETTING_DATABASE_SHOW:
			return { ... state, showDatabase: true };

		case SETTING_DATABASE_FINISH:
			return { ... state, showDatabase: false };

		case SETTING_DATABASE_START:
			return { ... state, showDatabase: action.arg === 'stop' ? false : true, database: { ... state.database, status: STATUS_IN_PROGRESS, inProgress: true, debug: false, reason: state.status === 'error' ? null : state.database.reason } };

		case SETTING_DATABASE_SUCCESS:
			return { ... state, database: { ... state.database, ... action.database } };

		case SETTING_DATABASE_COMPLETE:
			return { ... state, database: { ... state.database, inProgress: false } };

		case SETTING_DATABASE_FAILED:
			return { ... state, database: { ... state.database, status: STATUS_FAILED, error: action.error } };

		case SETTING_LOAD_START:
			return { ... state, loadStatus: STATUS_IN_PROGRESS };

		case SETTING_LOAD_SUCCESS:
			return { ... state, loadStatus: STATUS_COMPLETE, values: action.values, groups: action.groups, postTypes: action.postTypes, installed: action.installed, canDelete: action.canDelete };

		case SETTING_LOAD_FAILED:
			return { ... state, loadStatus: STATUS_FAILED, error: action.error };

		case SETTING_SAVING:
			return { ... state, saveStatus: STATUS_IN_PROGRESS };

		case SETTING_SAVED:
			return { ... state, saveStatus: STATUS_COMPLETE, values: action.values, groups: action.groups, installed: action.installed };

		case SETTING_SAVE_FAILED:
			return { ... state, saveStatus: STATUS_FAILED, error: action.error };

		case SETTING_LOAD_STATUS:
			return { ... state, pluginStatus: action.pluginStatus };
	}

	return state;
}
