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
	LOG_DISPLAY_SET,
} from './type';
import { tableAction, processRequest, directApi } from 'lib/store';
import { RedirectionApi } from 'lib/api';

const STATUS_LOG_ITEM = {
	saving: LOG_ITEM_SAVING,
	saved: LOG_ITEM_SAVED,
	failed: LOG_ITEM_FAILED,
	order: 'date',
	store: 'log',
};
const STATUS_LOG = {
	saving: LOG_LOADING,
	saved: LOG_LOADED,
	failed: LOG_FAILED,
	order: 'date',
	store: 'log',
};

export const deleteExact = ( filterBy, filter ) => ( dispatch, getState ) => directApi( RedirectionApi.log.deleteAll, dispatch, STATUS_LOG, { page: 0, filter, filterBy }, getState().log );
export const deleteAll = ( filterBy, filter ) => ( dispatch, getState ) => processRequest( RedirectionApi.log.deleteAll, dispatch, STATUS_LOG, { page: 0, filter, filterBy }, getState().log, table => {
	return { ... table, filter: '', filterBy: '' };
} );
export const performTableAction = ( action, ids, extra ) => tableAction( RedirectionApi.bulk.log, action, ids, STATUS_LOG_ITEM, extra );
export const getLogs = args => ( dispatch, getState ) => processRequest( RedirectionApi.log.list, dispatch, STATUS_LOG, args, getState().log );
export const loadLogs = ( params = {} ) => getLogs( params );

export const setOrderBy = ( orderby, direction ) => getLogs( { orderby, direction } );
export const setPage = page => getLogs( { page } );
export const setFilter = ( filterBy, filter ) => getLogs( { filterBy, filter, orderby: '', page: 0 } );
export const setSelected = items => ( { type: LOG_SET_SELECTED, items: items.map( parseInt ) } );
export const setAllSelected = onoff => ( { type: LOG_SET_ALL_SELECTED, onoff } );
export const setTable = table => getLogs( table );
export const setDisplay = ( displayType, displaySelected ) => ( { type: LOG_DISPLAY_SET, displayType, displaySelected } );
