/**
 * Internal dependencies
 */
import {
	MODULE_LOADING,
	MODULE_LOADED,
	MODULE_FAILED,
} from './type';
import getApi from 'lib/api';

const processRequest = ( action, dispatch, params ) => {
	getApi( action, params )
		.then( json => {
			dispatch( { type: MODULE_LOADED, rows: json } );
		} )
		.catch( error => {
			dispatch( { type: MODULE_FAILED, error } );
		} );

	return dispatch( { ... params, type: MODULE_LOADING } );
};

export const getModule = ( moduleId, moduleType ) => {
	return ( dispatch ) => {
		const params = { moduleId, moduleType };

		return processRequest( 'red_get_module', dispatch, params );
	};
};

export const downloadFile = url => document.location.href = url;

export const setModule = ( module, moduleData ) => {
	return ( dispatch, getState ) => {
		const state = getState().module;
		const params = { module, moduleData: Object.assign( {}, state.rows[ 2 ].data, moduleData ) };

		return processRequest( 'red_set_module', dispatch, params );
	};
};
