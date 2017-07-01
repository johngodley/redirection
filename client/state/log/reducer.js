/**
 * Internal dependencies
 */

import { setPageUrl } from 'lib/wordpress-url';

/**
 * Internal dependencies
 */

import {
	LOG_LOADED,
	LOG_LOADING,
	LOG_FAILED,
	LOG_SET_SELECTED,
	LOG_SET_ALL_SELECTED,
} from './type';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from 'state/settings/type';

const removeIfExists = ( current, newItems ) => {
	const newArray = [];

	for ( let x = 0; x < current.length; x++ ) {
		if ( newItems.indexOf( current[ x ] ) === -1 ) {
			newArray.push( current[ x ] );
		}
	}

	return newArray;
};

const setSelected = ( existing, newItems ) => {
	return removeIfExists( existing, newItems ).concat( removeIfExists( newItems, existing ) );
};

const filterObject = obj => {
	const newObj = {};

	Object.keys( obj ).forEach( key => {
		if ( obj[ key ] !== undefined ) {
			newObj[ key ] = obj[ key ];
		}
	} );

	return newObj;
};

export default function scene( state = {}, action ) {
	switch ( action.type ) {
		case LOG_SET_ALL_SELECTED:
			return { ... state, selected: action.onoff ? state.rows.map( item => item.id ) : [] };

		case LOG_SET_SELECTED:
			return { ... state, selected: setSelected( state.selected, action.items ) };

		case LOG_LOADING:
			const { orderBy, direction, page, perPage, logType, filter, filterBy } = action;

			setPageUrl( { orderBy, direction, offset: page, filter, filterBy }, { orderBy: 'date', direction: 'desc', offset: 0, filter: '', filterBy: '' } );

			return { ... state, ... filterObject( { orderBy, direction, page, perPage, logType, filter, filterBy } ), status: STATUS_IN_PROGRESS, selected: [] };

		case LOG_FAILED:
			return { ... state, status: STATUS_FAILED, error: action.error };

		case LOG_LOADED:
			return { ... state, rows: action.rows, status: STATUS_COMPLETE, total: action.total };
	}

	return state;
}
