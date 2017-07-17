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
import { mergeWithTable, removeDefaults } from 'lib/table';
import { translate as __ } from 'lib/locale';

const processRequest = ( action, dispatch, params = {}, table = {} ) => {
	const tableData = mergeWithTable( table, params );
	const data = removeDefaults( { ... table, ... params }, 'name' );

	getApi( action, data )
		.then( json => {
			dispatch( { type: GROUP_LOADED, ... json } );
		} )
		.catch( error => {
			dispatch( { type: GROUP_FAILED, error } );
		} );

	return dispatch( { table: tableData, type: GROUP_LOADING } );
};

export const getGroup = args => {
	return ( dispatch, getState ) => {
		const { table } = getState().group;

		return processRequest( 'red_get_group', dispatch, args, table );
	};
};

export const saveGroup = item => {
	return ( dispatch, getState ) => {
		const { table } = getState().group;

		if ( item.id === 0 ) {
			table.page = 0;
			table.orderBy = 'id';
			table.direction = 'desc';
		}

		getApi( 'red_set_group', removeDefaults( { ... table, ... item } ) )
			.then( json => {
				dispatch( { type: GROUP_ITEM_SAVED, item: json.item, items: json.items, total: json.total, saving: [ item.id ] } );
			} )
			.catch( error => {
				dispatch( { type: GROUP_ITEM_FAILED, error, item, saving: [ item.id ] } );
			} );

		return dispatch( { type: GROUP_ITEM_SAVING, table, item, saving: [ item.id ] } );
	};
};

export const performTableAction = ( action, ids ) => {
	return ( dispatch, getState ) => {
		const { table, total } = getState().group;
		const params = {
			items: ids ? [ ids ] : table.selected,
			bulk: action,
		};

		if ( action === 'delete' && table.page > 0 && table.perPage * table.page === total - 1 ) {
			table.page -= 1;
		}

		if ( action === 'delete' && ! confirm( __( 'Are you sure you want to delete this item?', 'Are you sure you want to delete these items?', { count: params.items.length } ) ) ) {
			return;
		}

		const tableData = mergeWithTable( table, params );
		const data = removeDefaults( { ... table, ... { items: params.items.join( ',' ), bulk: params.bulk } }, 'name' );

		getApi( 'red_group_action', data )
			.then( json => {
				dispatch( { type: GROUP_ITEM_SAVED, ... json, saving: params.items } );
			} )
			.catch( error => {
				dispatch( { type: GROUP_ITEM_FAILED, error, saving: params.items } );
			} );

		return dispatch( { type: GROUP_ITEM_SAVING, table: tableData, saving: params.items } );
	};
};

export const setOrderBy = ( orderBy, direction ) => getGroup( { orderBy, direction } );
export const setPage = page => getGroup( { page } );
export const setSearch = filter => getGroup( { filter, filterBy: '', page: 0 } );
export const setFilter = ( filterBy, filter ) => getGroup( { filterBy, filter } );

export const setSelected = items => ( { type: GROUP_SET_SELECTED, items: items.map( parseInt ) } );
export const setAllSelected = onoff => ( { type: GROUP_SET_ALL_SELECTED, onoff } );
