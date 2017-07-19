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
	GROUP_SET_ALL_SELECTED,
} from './type';
import { tableAction, saveAction, processRequest } from 'lib/store';

const STATUS_GROUP_ITEM = { saving: GROUP_ITEM_SAVING, saved: GROUP_ITEM_SAVED, failed: GROUP_ITEM_FAILED };
const STATUS_GROUP = { saving: GROUP_LOADING, saved: GROUP_LOADED, failed: GROUP_FAILED };

export const saveGroup = item => saveAction( 'red_set_group', item, STATUS_GROUP_ITEM );
export const performTableAction = ( action, ids ) => tableAction( 'red_group_action', action, ids, STATUS_GROUP_ITEM );
export const getGroup = args => ( dispatch, getState ) => processRequest( 'red_get_group', dispatch, STATUS_GROUP, args, getState().group.table );
export const setOrderBy = ( orderBy, direction ) => getGroup( { orderBy, direction } );
export const setPage = page => getGroup( { page } );
export const setSearch = filter => getGroup( { filter, filterBy: '', page: 0 } );
export const setFilter = ( filterBy, filter ) => getGroup( { filterBy, filter } );
export const setSelected = items => ( { type: GROUP_SET_SELECTED, items: items.map( parseInt ) } );
export const setAllSelected = onoff => ( { type: GROUP_SET_ALL_SELECTED, onoff } );
