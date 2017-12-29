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
	ERROR_SET_ALL_SELECTED,
} from './type';
import { tableAction, processRequest, directApi } from 'lib/store';
import { RedirectionApi } from 'lib/api';

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

export const deleteExact = ( filterBy, filter ) => ( dispatch, getState ) => directApi( RedirectionApi.error.deleteAll, dispatch, STATUS_ERROR, { page: 0, filter, filterBy }, getState().error );
export const deleteAll = ( filterBy, filter ) => ( dispatch, getState ) => processRequest( RedirectionApi.error.deleteAll, dispatch, STATUS_ERROR, { page: 0, filter, filterBy }, getState().error, table => {
	return { ... table, filter: '', filterBy: '' };
} );
export const performTableAction = ( action, ids, extra ) => tableAction( RedirectionApi.bulk.error, action, ids, STATUS_ERROR_ITEM, extra );
export const getLogs = args => dispatch => processRequest( RedirectionApi.error.list, dispatch, STATUS_ERROR, args );
export const loadLogs = () => getLogs( { filter: '', filterBy: '', page: 0, orderby: '' } );
export const setOrderBy = ( orderby, direction ) => getLogs( { orderby, direction } );
export const setPage = page => getLogs( { page } );
export const setSearch = ( filter, filterBy = '' ) => getLogs( { filter, filterBy: filter === '' ? '' : filterBy, page: 0, orderby: '' } );
export const setFilter = ( filterBy, filter ) => getLogs( { filterBy, filter, orderby: '', page: 0 } );
export const setSelected = items => ( { type: ERROR_SET_SELECTED, items: items.map( parseInt ) } );
export const setAllSelected = onoff => ( { type: ERROR_SET_ALL_SELECTED, onoff } );
