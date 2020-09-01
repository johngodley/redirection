/**
 * Internal dependencies
 */
import {
	GROUP_LOADING,
	GROUP_LOADED,
	GROUP_FAILED,
	GROUP_ITEM_SAVING,
	GROUP_ITEM_FAILED,
	GROUP_ITEM_SAVED,
	GROUP_SET_SELECTED,
	GROUP_DISPLAY_SET,
} from './type';
import { tableAction, createAction, updateAction, processRequest } from 'lib/store';
import { RedirectionApi } from 'lib/api-request';

const STATUS_GROUP_ITEM = {
	store: 'group',
	saving: GROUP_ITEM_SAVING,
	saved: GROUP_ITEM_SAVED,
	failed: GROUP_ITEM_FAILED,
	order: 'name',
};
const STATUS_GROUP = {
	store: 'group',
	saving: GROUP_LOADING,
	saved: GROUP_LOADED,
	failed: GROUP_FAILED,
	order: 'name',
};

export const createGroup = ( item ) => createAction( RedirectionApi.group.create, item, STATUS_GROUP_ITEM, 'name' );
export const updateGroup = ( id, item ) => updateAction( RedirectionApi.group.update, id, item, STATUS_GROUP_ITEM );
export const performTableAction = ( action, ids ) =>
	tableAction( RedirectionApi.bulk.group, action, ids, STATUS_GROUP_ITEM );
export const getGroup = ( args ) => ( dispatch, getState ) =>
	processRequest( RedirectionApi.group.list, dispatch, STATUS_GROUP, args, getState().group );
export const setOrderBy = ( orderby, direction ) => getGroup( { orderby, direction } );
export const setPage = ( page ) => getGroup( { page } );
export const setFilter = ( filterBy ) => getGroup( { filterBy, page: 0 } );
export const setSelected = ( items ) => ( { type: GROUP_SET_SELECTED, items } );
export const setTable = ( table ) => getGroup( table );
export const setDisplay = ( displayType, displaySelected ) => ( {
	type: GROUP_DISPLAY_SET,
	displayType,
	displaySelected,
} );
