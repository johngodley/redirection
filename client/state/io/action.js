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
import getApi from 'lib/api';

export const exportFile = ( module, format ) => dispatch => {
	const data = {
		module,
		format,
	};

	getApi( 'red_export_data', data )
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
	getApi( 'red_import_data', { group }, file )
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
	getApi( 'red_get_importers' )
		.then( resp => {
			dispatch( { type: IO_IMPORTERS, importers: resp.importers } );
		} )
		.catch( error => {
			dispatch( { type: IO_FAILED, error } );
		} );
};

export const pluginImport = plugin => dispatch => {
	getApi( 'red_get_importers', { plugin } )
		.then( resp => {
			dispatch( { type: IO_IMPORTED, total: resp.imported } );
		} )
		.catch( error => {
			dispatch( { type: IO_FAILED, error } );
		} );

	return dispatch( { type: IO_IMPORTING } );
};
