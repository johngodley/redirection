/**
 * Internal dependencies
 */
import {
	MAP_LOADING,
	MAP_LOADED,
	MAP_FAILED,
} from './type';
import { getApi, RedirectLiApi } from 'lib/api';

export const getMap = ip => ( dispatch, getState ) => {
	const { maps } = getState().map;

	if ( maps[ ip ] ) {
		return;
	}

	getApi( RedirectLiApi.ip.getGeo( ip ) )
		.then( map => {
			dispatch( { type: MAP_LOADED, map } );
		} )
		.catch( error => {
			dispatch( { type: MAP_FAILED, error } );
		} );

	return dispatch( { type: MAP_LOADING } );
};
