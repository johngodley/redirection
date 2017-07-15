/**
 * Internal dependencies
 */

import {
	GROUP_LOADED,
	GROUP_LOADING,
	GROUP_FAILED,
	GROUP_SET_SELECTED,
	GROUP_SET_ALL_SELECTED,
	GROUP_ITEM_SAVING,
	GROUP_ITEM_SAVED,
	GROUP_ITEM_FAILED,
} from './type';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from 'state/settings/type';
import { setTableParams, setTableSelected, setTableAllSelected } from 'lib/table';

const setGroup = ( existing, newGroup ) => {
	const dupe = existing.slice( 0 );

	for ( let x = 0; x < existing.length; x++ ) {
		if ( existing[ x ].id === newGroup.groupId ) {
			dupe[ x ] = Object.assign( {}, existing[ x ], { name: newGroup.name, module_id: newGroup.module_id } );
			break;
		}
	}

	return dupe;
};

const getGroup = action => ( { groupId: action.id, name: action.name, enabled: action.enabled, module_id: action.module_id } );

export default function groups( state = {}, action ) {
	switch ( action.type ) {
		case GROUP_ITEM_SAVING:
			return { ... state, saving: true, rows: setGroup( state.rows, action.group ), table: setTableParams( state.table, action, 'name' ) };

		case GROUP_ITEM_SAVED:
			return { ... state, saving: false, rows: action.items ? action.items : setGroup( state.rows, getGroup( action ) ), total: action.total ? action.total : state.total };

		case GROUP_ITEM_FAILED:
			return { ... state, saving: false };

		case GROUP_SET_ALL_SELECTED:
			return { ... state, table: setTableAllSelected( state.table, state.rows, action.onoff ) };

		case GROUP_SET_SELECTED:
			return { ... state, table: setTableSelected( state.table, action.items ) };

		case GROUP_LOADING:
			return { ... state, table: setTableParams( state.table, action, 'name' ), status: STATUS_IN_PROGRESS };

		case GROUP_FAILED:
			return { ... state, status: STATUS_FAILED };

		case GROUP_LOADED:
			return { ... state, rows: action.rows, status: STATUS_COMPLETE, total: action.total };
	}

	return state;
}
