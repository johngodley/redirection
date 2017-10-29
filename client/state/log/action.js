/**
 * Internal dependencies
 */
import {
	LOG_LOADING,
	LOG_LOADED,
	LOG_FAILED,
	LOG_ITEM_SAVING,
	LOG_ITEM_SAVED,
	LOG_ITEM_FAILED,
	LOG_SET_SELECTED,
	LOG_SET_ALL_SELECTED,
} from './type';
import { tableAction, processRequest, directApi } from 'lib/store';

const STATUS_LOG_ITEM = { saving: LOG_ITEM_SAVING, saved: LOG_ITEM_SAVED, failed: LOG_ITEM_FAILED, order: 'date' };
const STATUS_LOG = { saving: LOG_LOADING, saved: LOG_LOADED, failed: LOG_FAILED, order: 'date' };

export const deleteExact = ( filterBy, filter ) => ( dispatch, getState ) => directApi( 'red_delete_all', dispatch, STATUS_LOG, { page: 0, filter, filterBy, logType: getState().log.logType }, getState().log );
export const deleteAll = ( filterBy, filter ) => ( dispatch, getState ) => processRequest( 'red_delete_all', dispatch, STATUS_LOG, { page: 0, filter, filterBy, logType: getState().log.logType }, getState().log, table =>{
	return { ... table, filter: '', filterBy: '' };
} );
export const performTableAction = ( action, ids, extra ) => tableAction( 'log', 'red_log_action', action, ids, STATUS_LOG_ITEM, extra );
export const getLogs = args => ( dispatch, getState ) => {
	const { log } = getState();

	if ( log.requestCount === 0 ) {
		args = {};
	}

	return processRequest( 'red_get_logs', dispatch, STATUS_LOG, { ... args, logType: args.logType ? args.logType : log.logType }, log );
};
export const loadLogs = logType => getLogs( { logType, filter: '', filterBy: '', page: 0, orderBy: '' } );
export const setOrderBy = ( orderBy, direction ) => getLogs( { orderBy, direction } );
export const setPage = page => getLogs( { page } );
export const setSearch = ( filter, filterBy = '' ) => getLogs( { filter, filterBy: filter === '' ? '' : filterBy, page: 0, orderBy: '' } );
export const setFilter = ( filterBy, filter ) => getLogs( { filterBy, filter, orderBy: '', page: 0 } );
export const setSelected = items => ( { type: LOG_SET_SELECTED, items: items.map( parseInt ) } );
export const setAllSelected = onoff => ( { type: LOG_SET_ALL_SELECTED, onoff } );
