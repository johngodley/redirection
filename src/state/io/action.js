/**
 * Internal dependencies
 */
import {
	IO_EXPORTED,
	IO_EXPORTING,
	IO_IMPORTING,
	IO_IMPORTED,
	IO_FAILED,
	IO_CLEAR,
	IO_ADD_FILE,
	IO_IMPORTERS,
} from './type';
import { RedirectionApi } from '../../lib/api-request';
import { apiFetch } from '@wp-plugin-lib';

export const exportFile = ( module, format ) => dispatch => {
	apiFetch( RedirectionApi.export.file( module, format ) )
		.then( resp => {
			dispatch( { type: IO_EXPORTED, data: resp.data } );
		} )
		.catch( error => {
			dispatch( { type: IO_FAILED, error } );
		} );

	return dispatch( { type: IO_EXPORTING } );
};

export const downloadFile = url => {
	document.location.href = url;

	return {
		type: 'NOTHING',
	};
};

export const importFile = ( file, group ) => dispatch => {
	apiFetch( RedirectionApi.import.upload( group, file ) )
		.then( resp => {
			dispatch( { type: IO_IMPORTED, total: resp.imported } );
		} )
		.catch( error => {
			dispatch( { type: IO_FAILED, error } );
		} );

	return dispatch( { type: IO_IMPORTING, file } );
};

export const clearFile = () => ( { type: IO_CLEAR } );
export const addFile = file => ( { type: IO_ADD_FILE, file } );
export const loadImporters = () => dispatch => {
	apiFetch( RedirectionApi.import.pluginList() )
		.then( resp => {
			dispatch( { type: IO_IMPORTERS, importers: resp.importers } );
		} )
		.catch( error => {
			dispatch( { type: IO_FAILED, error } );
		} );
};

export const pluginImport = plugin => dispatch => {
	apiFetch( RedirectionApi.import.pluginImport( plugin ) )
		.then( resp => {
			dispatch( { type: IO_IMPORTED, total: resp.imported } );
		} )
		.catch( error => {
			dispatch( { type: IO_FAILED, error } );
		} );

	return dispatch( { type: IO_IMPORTING } );
};
