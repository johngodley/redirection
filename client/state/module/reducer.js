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
	const mergedRows = [];

	for ( let x = 0; x < existingRows.length; x++ ) {
		const name = existingRows[ x ].name;

		mergedRows[ x ] = {
			name,
			redirects: existingRows[ x ].redirects,
			data: existingRows[ x ].data,
			module_id: existingRows[ x ].module_id,
			displayName: existingRows[ x ].displayName,
		};

		if ( newRows[ name ] ) {
			mergedRows[ x ].redirects = parseInt( newRows[ name ].redirects, 10 );
			mergedRows[ x ].module_id = parseInt( newRows[ name ].module_id, 10 );
			mergedRows[ x ].data = newRows[ name ].data;
			mergedRows[ x ].displayName = newRows[ name ].displayName;
		}
	}

	for ( const moduleName in newRows ) {
		if ( ! mergedRows.find( item => item.name === moduleName ) ) {
			mergedRows.push( newRows[ moduleName ] );
		}
	}

	return mergedRows;
};

const setData = ( existing, module, moduleData ) => {
	const copied = existing.slice();

	for ( let x = 0; x < copied.length; x++ ) {
		if ( copied[ x ].name === module ) {
			copied[ x ].data = null;

			if ( moduleData ) {
				copied[ x ].data = Object.assign( {}, copied[ x ].data, moduleData );
			}

			break;
		}
	}

	return copied;
};

export default function scene( state = {}, action ) {
	switch ( action.type ) {
		case MODULE_LOADING:
			return { ... state, rows: setData( state.rows, action.module, action.moduleData ), status: STATUS_IN_PROGRESS };

		case MODULE_FAILED:
			return { ... state, status: STATUS_FAILED, error: action.error };

		case MODULE_LOADED:
			return { ... state, rows: mergeRows( state.rows, action.rows ), status: STATUS_COMPLETE };
	}

	return state;
}
