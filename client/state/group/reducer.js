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
	GROUP_DISPLAY_SET,
} from './type';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from 'state/settings/type';
import { setTableSelected, setTableAllSelected, clearSelected } from 'lib/table';
import { setTable, setRows, setTotal, setItem, setSaving, removeSaving, restoreToOriginal } from 'lib/store';

export default function redirects( state = {}, action ) {
	switch ( action.type ) {
		case GROUP_LOADING:
			return { ... state, table: setTable( state, action ), status: STATUS_IN_PROGRESS, saving: [] };

		case GROUP_LOADED:
			return { ... state, rows: setRows( state, action ), status: STATUS_COMPLETE, total: setTotal( state, action ), table: clearSelected( state.table ) };

		case GROUP_ITEM_SAVING:
			return { ... state, table: clearSelected( setTable( state, action ) ), saving: setSaving( state, action ), rows: setItem( state, action ) };

		case GROUP_ITEM_SAVED:
			return { ... state, rows: setRows( state, action ), total: setTotal( state, action ), saving: removeSaving( state, action ) };

		case GROUP_SET_ALL_SELECTED:
			return { ... state, table: setTableAllSelected( state.table, state.rows, action.onoff ) };

		case GROUP_SET_SELECTED:
			return { ... state, table: setTableSelected( state.table, action.items ) };

		case GROUP_FAILED:
			return { ... state, status: STATUS_FAILED, saving: [] };

		case GROUP_ITEM_FAILED:
			return { ... state, saving: removeSaving( state, action ), rows: restoreToOriginal( state, action ) };

		case GROUP_DISPLAY_SET:
			return { ... state, table: { ... state.table, displayType: action.displayType, displaySelected: action.displaySelected } };
	}

	return state;
}
