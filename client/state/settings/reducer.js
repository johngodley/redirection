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

	STATUS_IN_PROGRESS,
	STATUS_COMPLETE,
	STATUS_FAILED,
} from './type';

export default function scene( state = {}, action ) {
	switch ( action.type ) {
		case SETTING_LOAD_START:
			return { ... state, loadStatus: STATUS_IN_PROGRESS };

		case SETTING_LOAD_SUCCESS:
			return { ... state, loadStatus: STATUS_COMPLETE, values: action.values, groups: action.groups };

		case SETTING_LOAD_FAILED:
			return { ... state, loadStatus: STATUS_FAILED };

		case SETTING_SAVING:
			return { ... state, saveStatus: STATUS_IN_PROGRESS };

		case SETTING_SAVED:
			return { ... state, saveStatus: STATUS_COMPLETE, values: action.values, groups: action.groups };

		case SETTING_SAVE_FAILED:
			return { ... state, saveStatus: STATUS_FAILED };
	}

	return state;
}
