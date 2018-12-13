/**
 * Internal dependencies
 */

import {
	ERROR_LOADED,
	ERROR_LOADING,
	ERROR_FAILED,
	ERROR_ITEM_SAVING,
	ERROR_ITEM_SAVED,
	ERROR_ITEM_FAILED,
	ERROR_SET_SELECTED,
	ERROR_SET_ALL_SELECTED,
} from './type';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from 'state/settings/type';
import { setTableSelected, setTableAllSelected, clearSelected } from 'lib/table';
import { setTable, setRows, setTotal, setItem, setSaving, removeSaving, restoreToOriginal } from 'lib/store';

export default function log( state = {}, action ) {
	switch ( action.type ) {
		case ERROR_SET_ALL_SELECTED:
			return { ... state, table: setTableAllSelected( state.table, state.rows, action.onoff ) };

		case ERROR_SET_SELECTED:
			return { ... state, table: setTableSelected( state.table, action.items ) };

		case ERROR_ITEM_SAVING:
			return { ... state, table: clearSelected( setTable( state, action ) ), saving: setSaving( state, action ), rows: setItem( state, action ) };

		case ERROR_ITEM_SAVED:
			return { ... state, rows: setRows( state, action ), total: setTotal( state, action ), saving: removeSaving( state, action ) };

		case ERROR_LOADING:
			return { ... state, table: setTable( state, action ), status: STATUS_IN_PROGRESS, saving: [], requestCount: state.requestCount + 1 };

		case ERROR_FAILED:
			return { ... state, status: STATUS_FAILED, saving: [] };

		case ERROR_LOADED:
			return { ... state, rows: setRows( state, action ), status: STATUS_COMPLETE, total: setTotal( state, action ), table: clearSelected( state.table ) };

		case ERROR_ITEM_FAILED:
			return { ... state, saving: removeSaving( state, action ), rows: restoreToOriginal( state, action ) };
	}

	return state;
}
