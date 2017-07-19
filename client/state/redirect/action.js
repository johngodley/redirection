/**
 * Internal dependencies
 */
import {
	REDIRECT_LOADING,
	REDIRECT_LOADED,
	REDIRECT_FAILED,
	REDIRECT_ITEM_SAVING,
	REDIRECT_ITEM_FAILED,
	REDIRECT_ITEM_SAVED,
	REDIRECT_SET_SELECTED,
	REDIRECT_SET_ALL_SELECTED,
} from './type';
import { tableAction, saveAction, processRequest } from 'lib/store';

const STATUS_REDIRECT_ITEM = { saving: REDIRECT_ITEM_SAVING, saved: REDIRECT_ITEM_SAVED, failed: REDIRECT_ITEM_FAILED, order: 'name' };
const STATUS_REDIRECT = { saving: REDIRECT_LOADING, saved: REDIRECT_LOADED, failed: REDIRECT_FAILED, order: 'name' };

export const saveRedirect = item => saveAction( 'redirect', 'red_set_redirect', item, STATUS_REDIRECT_ITEM );
export const performTableAction = ( action, ids ) => tableAction( 'redirect', 'red_redirect_action', action, ids, STATUS_REDIRECT_ITEM );
export const getRedirect = args => ( dispatch, getState ) => processRequest( 'red_get_redirect', dispatch, STATUS_REDIRECT, args, getState().redirect.table );
export const setOrderBy = ( orderBy, direction ) => getRedirect( { orderBy, direction } );
export const setPage = page => getRedirect( { page } );
export const setSearch = filter => getRedirect( { filter, filterBy: '', page: 0 } );
export const setFilter = ( filterBy, filter ) => getRedirect( { filterBy, filter } );
export const setSelected = items => ( { type: REDIRECT_SET_SELECTED, items: items.map( parseInt ) } );
export const setAllSelected = onoff => ( { type: REDIRECT_SET_ALL_SELECTED, onoff } );
