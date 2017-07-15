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
import { mergeWithTable, mergeWithTableForApi } from 'lib/table';

const redirectCollect = json => ( { rows: json.items, total: json.total } );

const processRequest = ( action, dispatch, params = {}, table = {} ) => {
	const data = table ? mergeWithTableForApi( table, params ) : params;
	const reducer = table ? Object.assign( {}, mergeWithTable( table, params ), params ) : params;

	getApi( action, data )
		.then( json => {
			dispatch( { type: REDIRECT_LOADED, ... redirectCollect( json ) } );
		} )
		.catch( error => {
			console.error( error );
			dispatch( { type: REDIRECT_FAILED, error: error.message } );
		} );

	return dispatch( { ... reducer, type: REDIRECT_LOADING } );
};

export const getRedirect = args => {
	return ( dispatch, getState ) => {
		const { table } = getState().redirect;

		return processRequest( 'red_get_redirect', dispatch, args, table );
	};
};

export const setOrderBy = ( orderBy, direction ) => getRedirect( { orderBy, direction } );
export const setPage = page => getRedirect( { page } );
export const setSearch = filter => getRedirect( { filter, page: 0 } );
export const setFilter = ( filterBy, filter ) => getRedirect( { filterBy, filter } );

export const setSelected = items => ( { type: REDIRECT_SET_SELECTED, items } );
export const setAllSelected = onoff => ( { type: REDIRECT_SET_ALL_SELECTED, onoff } );

export const saveRedirect = redirect => {
	return ( dispatch, getState ) => {
		const { table } = getState().redirect;

		if ( redirect.id === 0 ) {
			table.page = 0;
			table.orderBy = 'id';
			table.direction = 'desc';
		}

		getApi( 'red_set_redirect', mergeWithTableForApi( table, redirect ) )
			.then( json => {
				dispatch( { type: REDIRECT_ITEM_SAVED, redirect: json } );
			} )
			.catch( error => {
				console.error( error );
				dispatch( { type: REDIRECT_ITEM_FAILED, error: error.message ? error.message : error } );
			} );

		return dispatch( { type: REDIRECT_ITEM_SAVING, ... table, redirect: redirect } );
	};
};

export const performTableAction = ( action, ids ) => {
	return ( dispatch, getState ) => {
		const { table, total } = getState().redirect;
		const params = {
			items: ids ? ids : table.selected.join( ',' ),
			bulk: action,
		};

		if ( action === 'delete' && params.page > 0 && params.perPage * params.page === total - 1 ) {
			params.page -= 1;
		}

		return processRequest( 'red_redirect_action', dispatch, params, table );
	};
};
