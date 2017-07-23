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
import { tableAction, processRequest } from 'lib/store';

const STATUS_LOG_ITEM = { saving: LOG_ITEM_SAVING, saved: LOG_ITEM_SAVED, failed: LOG_ITEM_FAILED, order: 'date' };
const STATUS_LOG = { saving: LOG_LOADING, saved: LOG_LOADED, failed: LOG_FAILED, order: 'date' };

export const deleteAll = () => ( dispatch, getState ) => processRequest( 'red_delete_all', dispatch, STATUS_LOG, { logType: getState().log.logType }, getState().log );
export const performTableAction = ( action, ids, extra ) => tableAction( 'log', 'red_log_action', action, ids, STATUS_LOG_ITEM, extra );
export const getLogs = args => ( dispatch, getState ) => {
	const { log } = getState();

	return processRequest( 'red_get_logs', dispatch, STATUS_LOG, { ... args, logType: args.logType ? args.logType : log.logType }, log );
};
export const loadLogs = logType => getLogs( { logType } );
export const setOrderBy = ( orderBy, direction ) => getLogs( { orderBy, direction } );
export const setPage = page => getLogs( { page } );
export const setSearch = filter => getLogs( { filter, filterBy: '', page: 0, orderBy: '' } );
export const setFilter = ( filterBy, filter ) => getLogs( { filterBy, filter, orderBy: '' } );
export const setSelected = items => ( { type: LOG_SET_SELECTED, items: items.map( parseInt ) } );
export const setAllSelected = onoff => ( { type: LOG_SET_ALL_SELECTED, onoff } );
