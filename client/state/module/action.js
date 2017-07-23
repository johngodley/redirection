/**
 * Internal dependencies
 */
import {
	MODULE_LOADING,
	MODULE_LOADED,
	MODULE_FAILED,
	MODULE_ITEM_SAVING,
	MODULE_ITEM_SAVED,
	MODULE_ITEM_FAILED,
} from './type';
import { saveAction, processRequest } from 'lib/store';

const STATUS_MODULE = { saving: MODULE_LOADING, saved: MODULE_LOADED, failed: MODULE_FAILED, order: 'name' };
const STATUS_MODULE_ITEM = { saving: MODULE_ITEM_SAVING, saved: MODULE_ITEM_SAVED, failed: MODULE_ITEM_FAILED, order: 'name' };

export const getModule = args => ( dispatch, getState ) => processRequest( 'red_get_module', dispatch, STATUS_MODULE, args, getState().module );
export const setModule = item => saveAction( 'module', 'red_set_module', item, STATUS_MODULE_ITEM );

export const downloadFile = url => {
	document.location.href = url;

	return {
		type: 'NOTHING',
	};
};
