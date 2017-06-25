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

const dispatchRequest = ( dispatch, action, params ) => {
	getApi( action, params )
		.then( data => data.json() )
		.then( json => {
			if ( json === 0 ) {
				throw 'Invalid data';
			} else if ( json.error ) {
				throw json.error;
			}

			dispatch( { type: LOG_LOADED, logs: json.items, total: json.total } );
		} )
		.catch( error => {
			dispatch( { type: LOG_FAILED, error } );
		} );

	return dispatch( { ... params, type: LOG_LOADING } );
};

const getLogs = args => {
	return ( dispatch, getState ) => {
		const { orderBy, direction, page, perPage, logType, filter, filterBy } = getState().log;
		const params = Object.assign( {}, { orderBy, direction, page, perPage, logType, filter, filterBy }, args );

		return dispatchRequest( dispatch, 'red_get_logs', params );
	};
};

export const setOrderBy = ( orderBy, direction ) => getLogs( { orderBy, direction } );
export const loadLogs = logType => getLogs( { logType } );
export const setPage = page => getLogs( { page } );
export const setSelected = items => ( { type: LOG_SET_SELECTED, items } );
export const setAllSelected = onoff => ( { type: LOG_SET_ALL_SELECTED, onoff } );
export const setSearch = filter => getLogs( { filter } );
export const setFilter = ( filterBy, filter ) => getLogs( { filterBy, filter } );

export const performTableAction = action => {
	return ( dispatch, getState ) => {
		const { orderBy, direction, page, perPage, logType, filter, filterBy } = getState().log;
		const params = Object.assign( {}, { orderBy, direction, page, perPage, logType, filter, filterBy }, {
			items: getState().log.selected,
			bulk: action,
		} );

		return dispatchRequest( dispatch, 'red_log_action', params );
	};
};

export const deleteAll = () => {
	return ( dispatch, getState ) => {
		const { logType } = getState().log;

		return dispatchRequest( dispatch, 'red_delete_all', { logType } );
	};
};
