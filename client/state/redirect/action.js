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
import getApi from 'lib/api';
import { mergeWithTable, removeDefaults } from 'lib/table';

const processRequest = ( action, dispatch, params = {}, table = {} ) => {
	const tableData = mergeWithTable( table, params );
	const data = removeDefaults( { ... table, ... params }, 'name' );

	getApi( action, data )
		.then( json => {
			dispatch( { type: REDIRECT_LOADED, ... json } );
		} )
		.catch( error => {
			dispatch( { type: REDIRECT_FAILED, error } );
		} );

	return dispatch( { table: tableData, type: REDIRECT_LOADING } );
};

export const getRedirect = args => {
	return ( dispatch, getState ) => {
		const { table } = getState().redirect;

		return processRequest( 'red_get_redirect', dispatch, args, table );
	};
};

export const saveRedirect = item => {
	return ( dispatch, getState ) => {
		const { table } = getState().redirect;

		if ( item.id === 0 ) {
			table.page = 0;
			table.orderBy = 'id';
			table.direction = 'desc';
		}

		getApi( 'red_set_redirect', removeDefaults( { ... table, ... item } ) )
			.then( json => {
				dispatch( { type: REDIRECT_ITEM_SAVED, item: json.item, items: json.items, total: json.total, saving: [ item.id ] } );
			} )
			.catch( error => {
				dispatch( { type: REDIRECT_ITEM_FAILED, error, item, saving: [ item.id ] } );
			} );

		return dispatch( { type: REDIRECT_ITEM_SAVING, table, item, saving: [ item.id ] } );
	};
};

export const performTableAction = ( action, ids ) => {
	return ( dispatch, getState ) => {
		const { table, total } = getState().redirect;
		const params = {
			items: ids ? [ ids ] : table.selected,
			bulk: action,
		};

		if ( action === 'delete' && table.page > 0 && table.perPage * table.page === total - 1 ) {
			table.page -= 1;
		}

		const tableData = mergeWithTable( table, params );
		const data = removeDefaults( { ... table, ... { items: params.items.join( ',' ), bulk: params.bulk } }, 'name' );

		getApi( 'red_redirect_action', data )
			.then( json => {
				dispatch( { type: REDIRECT_ITEM_SAVED, ... json, saving: params.items } );
			} )
			.catch( error => {
				dispatch( { type: REDIRECT_ITEM_FAILED, error, saving: params.items } );
			} );

		return dispatch( { type: REDIRECT_ITEM_SAVING, table: tableData, saving: params.items } );
	};
};

export const setOrderBy = ( orderBy, direction ) => getRedirect( { orderBy, direction } );
export const setPage = page => getRedirect( { page } );
export const setSearch = filter => getRedirect( { filter, filterBy: '', page: 0 } );
export const setFilter = ( filterBy, filter ) => getRedirect( { filterBy, filter } );

export const setSelected = items => ( { type: REDIRECT_SET_SELECTED, items: items.map( parseInt ) } );
export const setAllSelected = onoff => ( { type: REDIRECT_SET_ALL_SELECTED, onoff } );
