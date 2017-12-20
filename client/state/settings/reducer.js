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

	STATUS_IN_PROGRESS,
	STATUS_COMPLETE,
	STATUS_FAILED,
} from './type';

export default function settings( state = {}, action ) {
	switch ( action.type ) {
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
