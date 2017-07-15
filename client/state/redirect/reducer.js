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
import { setTableParams, setTableSelected, setTableAllSelected, clearSelected } from 'lib/table';

const setRedirect = ( existing, newRedirect ) => {
	const dupe = existing.slice( 0 );

	for ( let x = 0; x < existing.length; x++ ) {
		if ( parseInt( existing[ x ].id, 10 ) === newRedirect.id ) {
			dupe[ x ] = Object.assign( {}, dupe[ x ], newRedirect );
			break;
		}
	}

	return dupe;
};

export default function redirects( state = {}, action ) {
	switch ( action.type ) {
		case REDIRECT_ITEM_SAVING:
			return { ... state, saving: true, rows: setRedirect( state.rows, action.redirect ), table: action.refresh === false ? state.table : setTableParams( state.table, action, 'name' ) };

		case REDIRECT_ITEM_SAVED:
			return { ... state, saving: false, rows: action.redirect.items ? action.redirect.items : setRedirect( state.rows, action.redirect ), total: action.redirect.total ? action.redirect.total : state.total };

		case REDIRECT_ITEM_FAILED:
			return { ... state, saving: false };

		case REDIRECT_SET_ALL_SELECTED:
			return { ... state, table: setTableAllSelected( state.table, state.rows, action.onoff ) };

		case REDIRECT_SET_SELECTED:
			return { ... state, table: setTableSelected( state.table, action.items ) };

		case REDIRECT_LOADING:
			return { ... state, table: setTableParams( state.table, action, 'name' ), status: STATUS_IN_PROGRESS };

		case REDIRECT_FAILED:
			return { ... state, status: STATUS_FAILED };

		case REDIRECT_LOADED:
			return { ... state, rows: action.rows, status: STATUS_COMPLETE, total: action.total, table: clearSelected( state.table ) };
	}

	return state;
}
