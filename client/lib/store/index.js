/**
 * Internal dependencies
 */

import apiFetch from 'wp-plugin-lib/api-fetch';
import { mergeWithTable, removeDefaults } from 'lib/table';
import { translate as __ } from 'i18n-calypso';

export const tableAction = ( endpoint, bulk, ids, status, extra = {} ) => ( dispatch, getState ) => {
	const { table, total } = getState()[ status.store ];
	const params = {
		bulk,
	};

	if ( Array.isArray( ids ) ) {
		params.items = ids;
	} else if ( ids === false ) {
		params.items = table.selected;
	}

	if ( bulk === 'delete' && table.page > 0 && table.per_page * table.page === total - 1 ) {
		table.page -= 1;
	}

	const count = params.items ? params.items.length : total;

	if ( bulk === 'delete' && ! extra.deleteConfirm && ! confirm( __( 'Are you sure you want to delete this item?', 'Are you sure you want to delete the selected items?', { count } ) ) ) {
		return;
	}

	const tableData = mergeWithTable( table, params );
	const data = { items: params.items, ... extra };

	delete extra.deleteConfirm;

	if ( ids === true ) {
		data.global = true;
	}

	apiFetch( endpoint( bulk, data, removeDefaults( table, status.order ) ) )
		.then( json => {
			dispatch( { type: status.saved, ... json, saving: params.items ? params.items : [] } );
		} )
		.catch( error => {
			dispatch( { type: status.failed, error, saving: params.items ? params.items : [] } );
		} );

	return dispatch( { type: status.saving, table: tableData, saving: params.items ? params.items : [] } );
};

const doAction = ( endpoint, table, item, status, dispatch ) => {
	apiFetch( endpoint )
		.then( json => {
			dispatch( { type: status.saved, item: json.item, items: json.items, total: json.total, saving: [ item.id ] } );
		} )
		.catch( error => {
			dispatch( { type: status.failed, error, item, saving: [ item.id ] } );
		} );

	return dispatch( { type: status.saving, table, item, saving: [ item.id ] } );
};

export const createAction = ( endpoint, item, status, defaultOrder = 'id' ) => ( dispatch, getState ) => {
	const { table } = getState()[ status.store ];

	// Reset the table params so this goes to the top
	table.page = 0;
	table.orderby = defaultOrder;
	table.direction = 'desc';
	table.filterBy = {};
	table.groupBy = '';

	return doAction( endpoint( item, { orderby: 'id', direction: 'desc', per_page: table.per_page } ), table, item, status, dispatch );
};

export const updateAction = ( endpoint, id, item, status ) => ( dispatch, getState ) => {
	const { table } = getState()[ status.store ];

	return doAction( endpoint( id, item ), table, item, status, dispatch );
};

const objectDiff = ( source, extra ) => {
	const newObj = {};

	for ( const name in extra ) {
		if ( source[ name ] === undefined ) {
			newObj[ name ] = extra[ name ];
		}
	}

	return newObj;
};

const deepCompare = ( first, second ) => {
	for ( const name in first ) {
		if ( first [ name ] !== second[ name ] ) {
			return false;
		}
	}

	return true;
};

const removeEmpty = ( item ) =>
	Object.keys( item )
		.filter( ( key ) => item[ key ] )
		.reduce( ( newObj, key ) => {
			newObj[ key ] = item[ key ];
			return newObj;
		}, {} );

export const processRequest = ( endpoint, dispatch, status, params = {}, state = {}, reduxer = s => s ) => {
	const { table = {}, rows } = state;
	const tableData = reduxer( mergeWithTable( table, params ) );
	const data = removeEmpty( removeDefaults( { ... table, ... params }, status.order ) );

	// If it's the same as our current store then ignore
	if ( deepCompare( tableData, table ) && rows.length > 0 && deepCompare( params, {} ) ) {
		return;
	}

	apiFetch( endpoint( data ) )
		.then( json => {
			dispatch( { type: status.saved, ... json } );
		} )
		.catch( error => {
			dispatch( { type: status.failed, error } );
		} );

	return dispatch( { table: tableData, type: status.saving, ... objectDiff( tableData, params ) } );
};

export const directApi = ( endpoint, dispatch, status, params, state ) => {
	const { table } = state;
	const data = removeDefaults( { ... table, ... params }, status.order );

	apiFetch( endpoint( data ) )
		.then( json => {
			dispatch( { type: status.saved, ... json } );
		} )
		.catch( error => {
			dispatch( { type: status.failed, error } );
		} );
};

const copyReplace = ( data, item, cb ) => {
	const dupe = data.slice( 0 );

	for ( let x = 0; x < data.length; x++ ) {
		if ( parseInt( data[ x ].id, 10 ) === item.id ) {
			dupe[ x ] = cb( data[ x ] );
		}
	}

	return dupe;
};

export const setItem = ( state, action ) => action.item ? copyReplace( state.rows, action.item, existing => ( { ... existing, ... action.item, original: existing } ) ) : state.rows;
export const restoreToOriginal = ( state, action ) => action.item ? copyReplace( state.rows, action.item, existing => existing.original ) : state.rows;

export const setRows = ( state, action ) => {
	if ( action.item ) {
		return setItem( state, action );
	}

	if ( action.items ) {
		return action.items;
	}

	return state.rows;
};

export const setTable = ( state, action ) => action.table ? { ... state.table, ... action.table } : state.table;
export const setTotal = ( state, action ) => action.total !== undefined ? action.total : state.total;
export const setSaving = ( state, action ) => [ ... state.saving, ... action.saving ];
export const removeSaving = ( state, action ) => state.saving.filter( item => action.saving.indexOf( item ) === -1 );
