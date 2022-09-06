/**
 * Internal dependencies
 */

import {
	REDIRECT_LOADED,
	REDIRECT_LOADING,
	REDIRECT_FAILED,
	REDIRECT_SET_SELECTED,
	REDIRECT_ITEM_SAVING,
	REDIRECT_ITEM_SAVED,
	REDIRECT_ITEM_FAILED,
	REDIRECT_ADD_TOP,
	REDIRECT_DISPLAY_SET,
} from './type';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from '../../state/settings/type';
import { setTableSelected, clearSelected } from '../../lib/table';
import { setTable, setRows, setTotal, setItem, setSaving, removeSaving, restoreToOriginal } from '../../lib/store';

export default function redirects( state = {}, action ) {
	switch ( action.type ) {
		case REDIRECT_ADD_TOP:
			return { ... state, addTop: action.onoff };

		case REDIRECT_LOADING:
			return { ... state, table: setTable( state, action ), status: STATUS_IN_PROGRESS, saving: [] };

		case REDIRECT_LOADED:
			return { ... state, rows: setRows( state, action ), status: STATUS_COMPLETE, total: setTotal( state, action ), table: clearSelected( state.table ) };

		case REDIRECT_ITEM_SAVING:
			return { ... state, table: clearSelected( setTable( state, action ) ), saving: setSaving( state, action ), rows: setItem( state, action ) };

		case REDIRECT_ITEM_SAVED:
			return { ... state, rows: setRows( state, action ), total: setTotal( state, action ), saving: removeSaving( state, action ) };

		case REDIRECT_SET_SELECTED:
			return { ... state, table: setTableSelected( state.table, action.items, action.isEverything, state.rows ) };

		case REDIRECT_FAILED:
			return { ... state, status: STATUS_FAILED, saving: [] };

		case REDIRECT_ITEM_FAILED:
			return { ... state, saving: removeSaving( state, action ), rows: restoreToOriginal( state, action ) };

		case REDIRECT_DISPLAY_SET:
			return { ... state, table: { ... state.table, displayType: action.displayType, displaySelected: action.displaySelected } };
	}

	return state;
}
