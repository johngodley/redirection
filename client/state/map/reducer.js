/**
 * Internal dependencies
 */

import {
	MAP_LOADING,
	MAP_LOADED,
	MAP_FAILED,
} from './type';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from 'state/settings/type';

function getMaps( existing, detail ) {
	return { ... existing, [ detail.ip ]: detail };
}

export default function modules( state = {}, action ) {
	switch ( action.type ) {
		case MAP_LOADING:
			return { ... state, status: STATUS_IN_PROGRESS };

		case MAP_LOADED:
			return { ... state, status: STATUS_COMPLETE, maps: getMaps( state.maps, action.map ) };

		case MAP_FAILED:
			return { ... state, status: STATUS_FAILED, error: action.error };
	}

	return state;
}
