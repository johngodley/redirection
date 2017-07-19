/**
 * Internal dependencies
 */

import getApi from 'lib/api';
import { mergeWithTable, removeDefaults } from 'lib/table';
import { translate as __ } from 'lib/locale';

export const tableAction = ( storeName, action, bulk, ids, status ) => ( dispatch, getState ) => {
	const { table, total } = getState()[ storeName ];
	const params = {
		items: ids ? [ ids ] : table.selected,
		bulk,
	};

	if ( bulk === 'delete' && table.page > 0 && table.perPage * table.page === total - 1 ) {
		table.page -= 1;
	}

	if ( bulk === 'delete' && ! confirm( __( 'Are you sure you want to delete this item?', 'Are you sure you want to delete these items?', { count: params.items.length } ) ) ) {
		return;
	}

	const tableData = mergeWithTable( table, params );
	const data = removeDefaults( { ... table, ... { items: params.items.join( ',' ), bulk: params.bulk } }, status.order );

	getApi( action, data )
		.then( json => {
			dispatch( { type: status.saved, ... json, saving: params.items } );
		} )
		.catch( error => {
			dispatch( { type: status.failed, error, saving: params.items } );
		} );

	return dispatch( { type: status.saving, table: tableData, saving: params.items } );
};

export const saveAction = ( storeName, action, item, status ) => ( dispatch, getState ) => {
	const { table } = getState()[ storeName ];

	if ( item.id === 0 ) {
		// Reset the table params so this goes to the top
		table.page = 0;
		table.orderBy = 'id';
		table.direction = 'desc';
		table.filterBy = '';
		table.filter = '';
	}

	getApi( action, removeDefaults( { ... table, ... item } ) )
		.then( json => {
			dispatch( { type: status.saved, item: json.item, items: json.items, total: json.total, saving: [ item.id ] } );
		} )
		.catch( error => {
			dispatch( { type: status.failed, error, item, saving: [ item.id ] } );
		} );

	return dispatch( { type: status.saving, table, item, saving: [ item.id ] } );
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

export const processRequest = ( action, dispatch, status, params = {}, table = {} ) => {
	const tableData = mergeWithTable( table, params );
	const data = removeDefaults( { ... table, ... params }, status.order );

	getApi( action, data )
		.then( json => {
			dispatch( { type: status.saved, ... json } );
		} )
		.catch( error => {
			dispatch( { type: status.failed, error } );
		} );

	return dispatch( { table: tableData, type: status.saving, ... objectDiff( tableData, params ) } );
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
