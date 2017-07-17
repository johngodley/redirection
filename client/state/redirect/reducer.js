/**
 * Internal dependencies
 */

import {
	REDIRECT_LOADED,
	REDIRECT_LOADING,
	REDIRECT_FAILED,
	REDIRECT_SET_SELECTED,
	REDIRECT_SET_ALL_SELECTED,
	REDIRECT_ITEM_SAVING,
	REDIRECT_ITEM_SAVED,
	REDIRECT_ITEM_FAILED,
} from './type';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from 'state/settings/type';
import { setTableSelected, setTableAllSelected, clearSelected } from 'lib/table';

const copyReplace = ( data, item, cb ) => {
	const dupe = data.slice( 0 );

	for ( let x = 0; x < data.length; x++ ) {
		if ( parseInt( data[ x ].id, 10 ) === item.id ) {
			dupe[ x ] = cb( data[ x ] );
		}
	}

	return dupe;
};

const setRedirect = ( state, action ) => action.item ? copyReplace( state.rows, action.item, existing => ( { ... existing, ... action.item, original: existing } ) ) : state.rows;
const restoreToOriginal = ( state, action ) => action.item ? copyReplace( state.rows, action.item, existing => existing.original ) : state.rows;

const setRows = ( state, action ) => {
	if ( action.item ) {
		return setRedirect( state, action );
	}

	if ( action.items ) {
		return action.items;
	}

	return state.rows;
};

const setTable = ( state, action ) => action.table ? { ... state.table, ... action.table } : state.table;
const setTotal = ( state, action ) => action.total !== undefined ? action.total : state.total;
const setSaving = ( state, action ) => [ ... state.saving, ... action.saving ];
const removeSaving = ( state, action ) => state.saving.filter( item => action.saving.indexOf( item ) === -1 );

export default function redirects( state = {}, action ) {
	switch ( action.type ) {
		case REDIRECT_LOADING:
			return { ... state, table: setTable( state, action ), status: STATUS_IN_PROGRESS, saving: [] };

		case REDIRECT_LOADED:
			return { ... state, rows: setRows( state, action ), status: STATUS_COMPLETE, total: setTotal( state, action ), table: clearSelected( state.table ) };

		case REDIRECT_ITEM_SAVING:
			return { ... state, table: clearSelected( setTable( state, action ) ), saving: setSaving( state, action ), rows: setRedirect( state, action ) };

		case REDIRECT_ITEM_SAVED:
			return { ... state, rows: setRows( state, action ), total: setTotal( state, action ), saving: removeSaving( state, action ) };

		case REDIRECT_SET_ALL_SELECTED:
			return { ... state, table: setTableAllSelected( state.table, state.rows, action.onoff ) };

		case REDIRECT_SET_SELECTED:
			return { ... state, table: setTableSelected( state.table, action.items ) };

		case REDIRECT_FAILED:
			return { ... state, status: STATUS_FAILED, saving: [] };

		case REDIRECT_ITEM_FAILED:
			return { ... state, saving: removeSaving( state, action ), rows: restoreToOriginal( state, action ) };
	}

	return state;
}
