/**
 * Internal dependencies
 */

import {
	INFO_LOADING,
	INFO_LOADED_AGENT,
	INFO_LOADED_GEO,
	INFO_FAILED,
} from './type';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from 'state/settings/type';

function cacheData( existing, detail, type ) {
	return { ... existing, [ detail[ type ] ]: detail };
}

export default function modules( state = {}, action ) {
	switch ( action.type ) {
		case INFO_LOADING:
			return { ... state, status: STATUS_IN_PROGRESS };

		case INFO_LOADED_GEO:
			return { ... state, status: STATUS_COMPLETE, maps: cacheData( state.maps, action.map, 'ip' ) };

		case INFO_LOADED_AGENT:
			return { ... state, status: STATUS_COMPLETE, agents: cacheData( state.agents, action.agent, 'agent' ) };

		case INFO_FAILED:
			return { ... state, status: STATUS_FAILED, error: action.error };
	}

	return state;
}
