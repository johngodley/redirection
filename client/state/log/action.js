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
	LOG_DISPLAY_SET,
} from './type';
import { tableAction, processRequest } from 'lib/store';
import { RedirectionApi } from 'lib/api-request';

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

export const performTableAction = ( action, ids, extra ) =>
	tableAction( RedirectionApi.bulk.log, action, ids, STATUS_LOG_ITEM, extra );
export const getLogs = ( args ) => ( dispatch, getState ) =>
	processRequest( RedirectionApi.log.list, dispatch, STATUS_LOG, args, getState().log );
export const loadLogs = ( params = {} ) => getLogs( params );

export const setOrderBy = ( orderby, direction ) => getLogs( { orderby, direction } );
export const setPage = ( page ) => getLogs( { page } );
export const setFilter = ( filterBy ) => getLogs( { filterBy, page: 0 } );
export const setUngroupedFilter = ( filterBy ) => getLogs( { filterBy, page: 0, orderby: '', groupBy: '' } );
export const setSelected = ( items, isEverything = false ) => ( {
	type: LOG_SET_SELECTED,
	items,
	isEverything,
} );
export const setTable = ( table ) => getLogs( table );
export const setGroupBy = ( groupBy ) => getLogs( { groupBy, page: 0, orderby: 'total', direction: 'desc' } );
export const setDisplay = ( displayType, displaySelected ) => ( {
	type: LOG_DISPLAY_SET,
	displayType,
	displaySelected,
} );
