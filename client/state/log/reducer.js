/**
 * Internal dependencies
 */

import {
	LOG_LOADED,
	LOG_LOADING,
	LOG_FAILED,
	LOG_ITEM_SAVING,
	LOG_ITEM_SAVED,
	LOG_ITEM_FAILED,
	LOG_SET_SELECTED,
	LOG_DISPLAY_SET,
} from './type';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from 'state/settings/type';
import { setTableSelected, clearSelected } from 'lib/table';
import { setTable, setRows, setTotal, setItem, setSaving, removeSaving, restoreToOriginal } from 'lib/store';

export default function log( state = {}, action ) {
	switch ( action.type ) {
		case LOG_SET_SELECTED:
			return { ... state, table: setTableSelected( state.table, action.items, state.rows ) };

		case LOG_ITEM_SAVING:
			return { ... state, table: clearSelected( setTable( state, action ) ), saving: setSaving( state, action ), rows: setItem( state, action ) };

		case LOG_ITEM_SAVED:
			return { ... state, rows: setRows( state, action ), total: setTotal( state, action ), saving: removeSaving( state, action ) };

		case LOG_LOADING:
			return { ... state, table: setTable( state, action ), status: STATUS_IN_PROGRESS, saving: [], logType: action.logType, requestCount: state.requestCount + 1 };

		case LOG_FAILED:
			return { ... state, status: STATUS_FAILED, saving: [] };

		case LOG_LOADED:
			return { ... state, rows: setRows( state, action ), status: STATUS_COMPLETE, total: setTotal( state, action ), table: clearSelected( state.table ) };

		case LOG_ITEM_FAILED:
			return { ... state, saving: removeSaving( state, action ), rows: restoreToOriginal( state, action ) };

		case LOG_DISPLAY_SET:
			return { ... state, table: { ... state.table, displayType: action.displayType, displaySelected: action.displaySelected } };
	}

	return state;
}
