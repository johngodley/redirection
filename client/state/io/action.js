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
import { getApi, RedirectionApi } from 'lib/api';

export const exportFile = ( module, format ) => dispatch => {
	getApi( RedirectionApi.export.file( module, format ) )
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
	getApi( RedirectionApi.import.upload( group, file ) )
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
	getApi( RedirectionApi.import.pluginList() )
		.then( resp => {
			dispatch( { type: IO_IMPORTERS, importers: resp.importers } );
		} )
		.catch( error => {
			dispatch( { type: IO_FAILED, error } );
		} );
};

export const pluginImport = plugin => dispatch => {
	getApi( RedirectionApi.import.pluginImport( plugin ) )
		.then( resp => {
			dispatch( { type: IO_IMPORTED, total: resp.imported } );
		} )
		.catch( error => {
			dispatch( { type: IO_FAILED, error } );
		} );

	return dispatch( { type: IO_IMPORTING } );
};
