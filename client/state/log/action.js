/**
 * Internal dependencies
 */
import {
	LOG_LOADING,
	LOG_LOADED,
	LOG_FAILED,
	LOG_SET_SELECTED,
	LOG_SET_ALL_SELECTED,
} from './type';
import getApi from 'lib/api';
import { mergeWithTable, mergeWithTableForApi } from 'lib/table';

const logCollect = json => ( { rows: json.items, total: json.total } );

const dispatchRequest = ( dispatch, action, params, table = false ) => {
	const data = table ? mergeWithTableForApi( table, params ) : params;
	const reducer = table ? Object.assign( {}, mergeWithTable( table, params ), params ) : params;

	getApi( action, data )
		.then( json => {
			dispatch( { type: LOG_LOADED, ... logCollect( json ) } );
		} )
		.catch( error => {
			dispatch( { type: LOG_FAILED, error } );
		} );

	return dispatch( { ... reducer, type: LOG_LOADING } );
};

const getLogs = args => {
	return ( dispatch, getState ) => {
		const { table, logType } = getState().log;

		return dispatchRequest( dispatch, 'red_get_logs', { logType, ... args }, table );
	};
};

export const deleteAll = () => {
	return ( dispatch, getState ) => {
		const { logType } = getState().log;

		return dispatchRequest( dispatch, 'red_delete_all', { logType } );
	};
};

export const loadLogs = logType => getLogs( { logType } );

export const setOrderBy = ( orderBy, direction ) => getLogs( { orderBy, direction } );
export const setPage = page => getLogs( { page } );
export const setSearch = filter => getLogs( { filter, page: 0 } );
export const setFilter = ( filterBy, filter ) => getLogs( { filterBy, filter } );

export const setSelected = items => ( { type: LOG_SET_SELECTED, items } );
export const setAllSelected = onoff => ( { type: LOG_SET_ALL_SELECTED, onoff } );

export const performTableAction = ( action, ids ) => {
	return ( dispatch, getState ) => {
		const { table, total, logType } = getState().log;
		const params = {
			items: ids ? ids : table.selected.join( ',' ),
			bulk: action,
			logType,
		};

		if ( action === 'delete' && params.page > 0 && params.perPage * params.page === total - 1 ) {
			params.page -= 1;
		}

		return dispatchRequest( dispatch, 'red_log_action', params, table );
	};
};
