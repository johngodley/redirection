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
import getApi from 'lib/api';
import { mergeWithTable } from 'lib/table';

const groupCollect = json => ( { rows: json.items, total: json.total } );

const processRequest = ( action, dispatch, params = {} ) => {
	getApi( action, params )
		.then( json => {
			dispatch( { type: GROUP_LOADED, ... groupCollect( json ) } );
		} )
		.catch( error => {
			dispatch( { type: GROUP_FAILED, error } );
		} );

	return dispatch( { ... params, type: GROUP_LOADING } );
};

export const getGroup = args => {
	return ( dispatch, getState ) => {
		const { table } = getState().group;
		const params = mergeWithTable( { ... args }, table );

		return processRequest( 'red_get_group', dispatch, params );
	};
};

export const setOrderBy = ( orderBy, direction ) => getGroup( { orderBy, direction } );
export const setPage = page => getGroup( { page } );
export const setSearch = filter => getGroup( { filter, page: 0 } );
export const setFilter = ( filterBy, filter ) => getGroup( { filterBy, filter } );

export const setSelected = items => ( { type: GROUP_SET_SELECTED, items } );
export const setAllSelected = onoff => ( { type: GROUP_SET_ALL_SELECTED, onoff } );

export const saveGroup = ( groupId, name, moduleId ) => {
	return ( dispatch, getState ) => {
		const data = { groupId, name, moduleId };
		const { table } = getState().group;

		getApi( 'red_set_group', data )
			.then( json => {
				dispatch( { type: GROUP_ITEM_SAVED, ... json } );
			} )
			.catch( error => {
				dispatch( { type: GROUP_ITEM_FAILED, error } );
			} );

		return dispatch( { type: GROUP_ITEM_SAVING, ... mergeWithTable( {
			group: data,
		}, table ) } );
	};
};

export const performTableAction = ( action, ids ) => {
	return ( dispatch, getState ) => {
		const { table } = getState().group;
		const params = mergeWithTable( {
			items: ids ? ids : table.selected.join( ',' ),
			bulk: action,
		}, table );

		return processRequest( 'red_group_action', dispatch, params );
	};
};

export const createGroup = ( name, moduleId ) => {
	return ( dispatch, getState ) => {
		const { table } = getState().group;
		const params = mergeWithTable( {
			name,
			moduleId,
		}, table );

		params.orderBy = 'id';
		params.direction = 'desc';

		getApi( 'red_create_group', params )
			.then( json => {
				dispatch( { type: GROUP_ITEM_SAVED, ... json } );
			} )
			.catch( error => {
				dispatch( { type: GROUP_ITEM_FAILED, error } );
			} );

		return dispatch( { type: GROUP_ITEM_SAVING, ... params } );
	};
};