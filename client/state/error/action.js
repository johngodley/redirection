/**
 * Internal dependencies
 */
import {
	ERROR_LOADING,
	ERROR_LOADED,
	ERROR_FAILED,
	ERROR_ITEM_SAVING,
	ERROR_ITEM_SAVED,
	ERROR_ITEM_FAILED,
	ERROR_SET_SELECTED,
	ERROR_DISPLAY_SET,
} from './type';
import { tableAction, processRequest } from 'lib/store';
import { RedirectionApi } from 'lib/api-request';

const STATUS_ERROR_ITEM = {
	saving: ERROR_ITEM_SAVING,
	saved: ERROR_ITEM_SAVED,
	failed: ERROR_ITEM_FAILED,
	order: 'date',
	store: 'error',
};
const STATUS_ERROR = {
	saving: ERROR_LOADING,
	saved: ERROR_LOADED,
	failed: ERROR_FAILED,
	order: 'date',
	store: 'error',
};

export const performTableAction = ( action, ids, extra ) =>
	tableAction( RedirectionApi.bulk.error, action, ids, STATUS_ERROR_ITEM, extra );
export const getLogs = ( args ) => ( dispatch, getState ) =>
	processRequest( RedirectionApi.error.list, dispatch, STATUS_ERROR, args, getState().error );
export const loadLogs = ( params = {} ) => getLogs( params );
export const setOrderBy = ( orderby, direction ) => getLogs( { orderby, direction } );
export const setPage = ( page ) => getLogs( { page } );
export const setUngroupedFilter = ( filterBy ) => getLogs( { filterBy, page: 0, orderby: '', groupBy: '' } );
export const setFilter = ( filterBy ) => getLogs( { filterBy, page: 0 } );
export const setSelected = ( items ) => ( { type: ERROR_SET_SELECTED, items } );
export const setGroupBy = ( groupBy ) =>
	getLogs( { groupBy, page: 0, orderby: 'total', direction: 'desc' } );
export const setTable = ( table ) => getLogs( table );
export const setDisplay = ( displayType, displaySelected ) => ( {
	type: ERROR_DISPLAY_SET,
	displayType,
	displaySelected,
} );
