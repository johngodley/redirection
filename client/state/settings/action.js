/* global fetch */
/**
 * Internal dependencies
 */
import {
	SETTING_LOAD_START,
	SETTING_LOAD_SUCCESS,
	SETTING_LOAD_FAILED,
	SETTING_SAVING,
	SETTING_SAVED,
	SETTING_SAVE_FAILED,
	STATUS_COMPLETE,
	SETTING_LOAD_STATUS,
	SETTING_DATABASE_START,
	SETTING_DATABASE_FAILED,
	SETTING_DATABASE_SUCCESS,
	SETTING_DATABASE_SHOW,
	SETTING_DATABASE_FINISH,
	SETTING_API_SUCCESS,
	SETTING_API_FAILED,
	SETTING_API_TRY,
} from './type';
import { getApi, RedirectionApi } from 'lib/api';

export const loadSettings = () => ( dispatch, getState ) => {
	if ( getState().settings.loadStatus === STATUS_COMPLETE ) {
		return null;
	}

	getApi( RedirectionApi.setting.get() )
		.then( json => {
			dispatch( { type: SETTING_LOAD_SUCCESS, values: json.settings, groups: json.groups, postTypes: json.post_types, installed: json.installed, canDelete: json.canDelete } );
		} )
		.catch( error => {
			dispatch( { type: SETTING_LOAD_FAILED, error } );
		} );

	return dispatch( { type: SETTING_LOAD_START } );
};

export const saveSettings = settings => dispatch => {
	getApi( RedirectionApi.setting.update( settings ) )
		.then( json => {
			dispatch( { type: SETTING_SAVED, values: json.settings, groups: json.groups, installed: json.installed } );
		} )
		.catch( error => {
			dispatch( { type: SETTING_SAVE_FAILED, error } );
		} );

	return dispatch( { type: SETTING_SAVING } );
};

export const deletePlugin = () => dispatch => {
	getApi( RedirectionApi.plugin.delete() )
		.then( json => {
			document.location.href = json.location;
		} )
		.catch( error => {
			dispatch( { type: SETTING_SAVE_FAILED, error } );
		} );

	return dispatch( { type: SETTING_SAVING } );
};

export const loadStatus = () => dispatch => {
	getApi( RedirectionApi.plugin.status() )
		.then( json => {
			dispatch( { type: SETTING_LOAD_STATUS, pluginStatus: json } );
		} )
		.catch( error => {
			dispatch( { type: SETTING_LOAD_FAILED, error } );
		} );

	return dispatch( { type: SETTING_LOAD_START } );
};

export const fixStatus = () => dispatch => {
	getApi( RedirectionApi.plugin.fix() )
		.then( json => {
			dispatch( { type: SETTING_LOAD_STATUS, pluginStatus: json } );
		} )
		.catch( error => {
			dispatch( { type: SETTING_LOAD_FAILED, error } );
		} );

	return dispatch( { type: SETTING_LOAD_START } );
};

export const showUpgrade = () => ( { type: SETTING_DATABASE_SHOW } );

export const upgradeDatabase = ( arg ) => dispatch => {
	getApi( RedirectionApi.plugin.upgradeDatabase( arg ) )
		.then( json => {
			dispatch( { type: SETTING_DATABASE_SUCCESS, database: json } );
		} )
		.catch( error => {
			dispatch( { type: SETTING_DATABASE_FAILED, error } );
		} );

	return dispatch( { type: SETTING_DATABASE_START, arg } );
};

export const finishUpgrade = () => ( { type: SETTING_DATABASE_FINISH } );

export const checkApi = api => dispatch => {
	for ( let index = 0; index < api.length; index++ ) {
		const { id, url } = api[ index ];

		dispatch( { type: SETTING_API_TRY, id, method: 'GET' } );
		dispatch( { type: SETTING_API_TRY, id, method: 'POST' } );

		// Bit of a delay otherwise it can seem too fast...
		setTimeout( () => {
			// GET test
			getApi( RedirectionApi.plugin.checkApi( url ) )
				.then( () => {
					dispatch( { type: SETTING_API_SUCCESS, id, method: 'GET' } );
				} )
				.catch( error => {
					dispatch( { type: SETTING_API_FAILED, id, method: 'GET', error } );
				} );

			// POST test
			getApi( RedirectionApi.plugin.checkApi( url, true ) )
				.then( () => {
					dispatch( { type: SETTING_API_SUCCESS, id, method: 'POST' } );
				} )
				.catch( error => {
					dispatch( { type: SETTING_API_FAILED, id, method: 'POST', error } );
				} );
		}, 1000 );
	}
};
