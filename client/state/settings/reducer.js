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
	SETTING_API_SUCCESS,
	SETTING_API_FAILED,
	SETTING_API_TRY,

	STATUS_IN_PROGRESS,
	STATUS_COMPLETE,
	STATUS_FAILED,
} from './type';

function setApiTest( existing, id, method, result ) {
	const api = existing[ id ] ? { ... existing[ id ] } : [];

	api[ method ] = result;

	return { [ id ]: api };
}

export default function settings( state = {}, action ) {
	switch ( action.type ) {
		case SETTING_API_TRY:
			return { ... state, apiTest: { ... state.apiTest, ... setApiTest( state.apiTest, action.id, action.method, undefined ) } };

		case SETTING_API_SUCCESS:
			return { ... state, apiTest: { ... state.apiTest, ... setApiTest( state.apiTest, action.id, action.method, true ) } };

		case SETTING_API_FAILED:
			return { ... state, apiTest: { ... state.apiTest, ... setApiTest( state.apiTest, action.id, action.method, action.error.request.status ) } };

		case SETTING_DATABASE_SHOW:
			return { ... state, showDatabase: true };

		case SETTING_DATABASE_FINISH:
			return { ... state, showDatabase: false, database: { ... state.database, status: 'ok' } };

		case SETTING_DATABASE_START:
			return { ... state, database: { ... state.database, inProgress: true, result: 'ok', reason: action.arg === 'skip' ? false : state.database.reason }, showDatabase: action.arg === 'stop' ? false : true };

		case SETTING_DATABASE_SUCCESS:
			return { ... state, database: { ... state.database, ... action.database } };

		case SETTING_DATABASE_COMPLETE:
			return { ... state, database: { ... state.database, inProgress: false } };

		case SETTING_DATABASE_FAILED:
			return { ... state, database: { ... state.database, result: STATUS_FAILED, reason: action.error } };

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
