/**
 * Internal dependencies
 */

/**
 * Internal dependencies
 */

import {
	MODULE_LOADING,
	MODULE_LOADED,
	MODULE_FAILED,
} from './type';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from 'state/settings/type';

const mergeRows = ( existingRows, newRows ) => {
	const mergedRows = existingRows.slice( 0 );

	for ( let x = 0; x < newRows.length; x++ ) {
		const pos = mergedRows.findIndex( item => item.module_id === newRows[ x ].module_id );

		if ( pos === -1 ) {
			mergedRows.push( newRows[ x ] );
		} else {
			mergedRows[ pos ] = Object.assign( {}, newRows[ x ], mergedRows[ pos ] );
		}
	}

	return mergedRows;
};

export default function modules( state = {}, action ) {
	switch ( action.type ) {
		case MODULE_LOADING:
			return { ... state, status: STATUS_IN_PROGRESS };

		case MODULE_FAILED:
			return { ... state, status: STATUS_FAILED };

		case MODULE_LOADED:
			return { ... state, rows: mergeRows( state.rows, action.rows ), status: STATUS_COMPLETE };
	}

	return state;
}
