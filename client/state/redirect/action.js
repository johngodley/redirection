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
	REDIRECT_ADD_TOP,
	REDIRECT_DISPLAY_SET,
} from './type';
import { tableAction, createAction, updateAction, processRequest } from 'lib/store';
import { RedirectionApi } from 'lib/api-request';

const STATUS_REDIRECT_ITEM = {
	store: 'redirect',
	saving: REDIRECT_ITEM_SAVING,
	saved: REDIRECT_ITEM_SAVED,
	failed: REDIRECT_ITEM_FAILED,
	order: 'name',
};
const STATUS_REDIRECT = {
	store: 'redirect',
	saving: REDIRECT_LOADING,
	saved: REDIRECT_LOADED,
	failed: REDIRECT_FAILED,
	order: 'name',
};

export const createRedirect = ( item ) => createAction( RedirectionApi.redirect.create, item, STATUS_REDIRECT_ITEM );
export const updateRedirect = ( id, item ) =>
	updateAction( RedirectionApi.redirect.update, id, item, STATUS_REDIRECT_ITEM );
export const performTableAction = ( action, ids ) =>
	tableAction( RedirectionApi.bulk.redirect, action, ids, STATUS_REDIRECT_ITEM );
export const getRedirect = ( args ) => ( dispatch, getState ) =>
	processRequest( RedirectionApi.redirect.list, dispatch, STATUS_REDIRECT, args, getState().redirect );
export const setOrderBy = ( orderby, direction ) => getRedirect( { orderby, direction } );
export const setPage = ( page ) => getRedirect( { page } );
export const setFilter = ( filterBy ) => getRedirect( { filterBy, page: 0 } );
export const setSelected = ( items, isEverything = false ) => ( {
	type: REDIRECT_SET_SELECTED,
	items,
	isEverything,
} );
export const addToTop = ( onoff ) => ( { type: REDIRECT_ADD_TOP, onoff } );
export const setTable = ( table ) => getRedirect( table );
export const setDisplay = ( displayType, displaySelected ) => ( {
	type: REDIRECT_DISPLAY_SET,
	displayType,
	displaySelected,
} );
