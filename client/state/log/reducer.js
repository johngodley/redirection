/**
 * Internal dependencies
 */

import {
	LOG_LOADED,
	LOG_LOADING,
	LOG_FAILED,
	LOG_SET_SELECTED,
	LOG_SET_ALL_SELECTED,
} from './type';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from 'state/settings/type';
import { mergeWithTable, setTableSelected, setTableAllSelected } from 'lib/table';

export default function log( state = {}, action ) {
	switch ( action.type ) {
		case LOG_SET_ALL_SELECTED:
			return { ... state, table: setTableAllSelected( state.table, state.rows, action.onoff ) };

		case LOG_SET_SELECTED:
			return { ... state, table: setTableSelected( state.table, action.items ) };

		case LOG_LOADING:
			const { logType } = action;

			return { ... state, table: mergeWithTable( state.table, action, 'date' ), logType, status: STATUS_IN_PROGRESS, error: false };

		case LOG_FAILED:
			return { ... state, status: STATUS_FAILED, error: action.error };

		case LOG_LOADED:
			return { ... state, rows: action.rows, status: STATUS_COMPLETE, total: action.total };
	}

	return state;
}
