/**
 * Internal dependencies
 */

/**
 * Internal dependencies
 */

import {
	MODULE_LOADING,
	MODULE_LOADED,
	MODULE_FAILED,
	MODULE_ITEM_SAVING,
	MODULE_ITEM_SAVED,
	MODULE_ITEM_FAILED,
} from './type';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from 'state/settings/type';
import { setTable, setRows, setTotal, setItem, setSaving, removeSaving, restoreToOriginal } from 'lib/store';

export default function modules( state = {}, action ) {
	switch ( action.type ) {
		case MODULE_LOADING:
			return { ... state, table: setTable( state, action ), status: STATUS_IN_PROGRESS, saving: [] };

		case MODULE_FAILED:
			return { ... state, status: STATUS_FAILED };

		case MODULE_LOADED:
			return { ... state, rows: setRows( state, action ), status: STATUS_COMPLETE, total: setTotal( state, action ) };

		case MODULE_ITEM_SAVING:
			return { ... state, saving: setSaving( state, action ), rows: setItem( state, action ) };

		case MODULE_ITEM_SAVED:
			return { ... state, rows: setRows( state, action ), total: setTotal( state, action ), saving: removeSaving( state, action ) };

		case MODULE_ITEM_FAILED:
			return { ... state, saving: removeSaving( state, action ), rows: restoreToOriginal( state, action ) };
	}

	return state;
}
