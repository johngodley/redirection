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
} from './type';
import getApi from 'lib/api';

export const loadSettings = () => {
	return dispatch => {
		getApi( 'red_load_settings' )
			.then( data => data.json() )
			.then( json => {
				if ( json === 0 ) {
					throw 'Invalid data';
				}

				dispatch( { type: SETTING_LOAD_SUCCESS, values: json.settings, groups: json.groups } );
			} )
			.catch( error => {
				dispatch( { type: SETTING_LOAD_FAILED, error } );
			} );

		return dispatch( { type: SETTING_LOAD_START } );
	};
};

export const saveSettings = settings => {
	return dispatch => {
		getApi( 'red_save_settings', settings )
			.then( data => data.json() )
			.then( json => {
				if ( json === 0 ) {
					throw 'Invalid data';
				}

				dispatch( { type: SETTING_SAVED, values: json.settings, groups: json.groups } );
			} )
			.catch( error => {
				dispatch( { type: SETTING_SAVE_FAILED, error } );
			} );

		return dispatch( { type: SETTING_SAVING } );
	};
};

export const deletePlugin = () => {
	return dispatch => {
		getApi( 'red_delete_plugin' )
			.then( data => data.json() )
			.then( json => {
				document.location.href = json.location;
			} );

		return dispatch( { type: SETTING_SAVING } );
	};
};
