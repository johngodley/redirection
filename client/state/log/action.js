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
import { mergeWithTable } from 'lib/table';

const logCollect = json => ( { rows: json.items, total: json.total } );

const dispatchRequest = ( dispatch, action, params ) => {
	getApi( action, params )
		.then( json => {
			dispatch( { type: LOG_LOADED, ... logCollect( json ) } );
		} )
		.catch( error => {
			dispatch( { type: LOG_FAILED, error } );
		} );

	return dispatch( { ... params, type: LOG_LOADING } );
};

const getLogs = args => {
	return ( dispatch, getState ) => {
		const { logType, table } = getState().log;
		const params = mergeWithTable( { logType, ... args }, table );

		return dispatchRequest( dispatch, 'red_get_logs', params );
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

export const performTableAction = action => {
	return ( dispatch, getState ) => {
		const { table } = getState().log;
		const params = mergeWithTable( {
			items: table.selected.join( ',' ),
			bulk: action,
		}, table );

		return dispatchRequest( dispatch, 'red_log_action', params );
	};
};
