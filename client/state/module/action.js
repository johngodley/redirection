/**
 * Internal dependencies
 */
import {
	MODULE_LOADING,
	MODULE_LOADED,
	MODULE_FAILED,
	MODULE_SAVING,
	MODULE_SAVED,
} from './type';
import getApi from 'lib/api';

export const getModule = ( moduleId, moduleType ) => {
	return ( dispatch ) => {
		const params = { moduleId, moduleType };

		getApi( 'red_get_module', params )
			.then( json => {
				dispatch( { type: MODULE_LOADED, rows: json } );
			} )
			.catch( error => {
				dispatch( { type: MODULE_FAILED, error } );
			} );

		return dispatch( { ... params, type: MODULE_LOADING } );
	};
};

export const downloadFile = url => {
	document.location.href = url;

	return {
		type: 'NOTHING',
	};
};

export const setModule = ( module, moduleData ) => {
	return ( dispatch, getState ) => {
		const state = getState().module;
		const params = { module, moduleData: Object.assign( {}, state.rows[ 2 ].data, moduleData ) };

		getApi( 'red_set_module', params )
			.then( json => {
				dispatch( { type: MODULE_SAVED, rows: json } );
			} )
			.catch( error => {
				dispatch( { type: MODULE_FAILED, error } );
			} );

		return dispatch( { ... params, type: MODULE_SAVING } );
	};
};
