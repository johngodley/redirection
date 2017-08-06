/**
 * Internal dependencies
 */

/**
 * Internal dependencies
 */

import {
	IO_EXPORTING,
	IO_EXPORTED,
	IO_IMPORTING,
	IO_IMPORTED,
	IO_FAILED,
	IO_CLEAR,
	IO_ADD_FILE,
} from './type';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE } from 'state/settings/type';

export default function modules( state = {}, action ) {
	switch ( action.type ) {
		case IO_EXPORTING:
			return { ... state, exportStatus: STATUS_IN_PROGRESS };

		case IO_EXPORTED:
			return { ... state, exportStatus: STATUS_COMPLETE, exportData: action.data };

		case IO_ADD_FILE:
			return { ... state, file: action.file };

		case IO_CLEAR:
			return { ... state, file: false, lastImport: false, exportData: false };

		case IO_FAILED:
			return { ... state, importingStatus: STATUS_FAILED, exportStatus: STATUS_FAILED, lastImport: false, file: false, exportData: false };

		case IO_IMPORTING:
			return { ... state, importingStatus: STATUS_IN_PROGRESS, lastImport: false, file: action.file };

		case IO_IMPORTED:
			return { ... state, lastImport: action.total, importingStatus: STATUS_COMPLETE, file: false };
	}

	return state;
}
