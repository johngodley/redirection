/* global fetch, Redirectioni10n */
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

const getApi = ( action, data ) => {
	const form = new FormData();

	form.append( 'action', action );
	form.append( '_wpnonce', Redirectioni10n.WP_API_nonce );

	if ( data ) {
		for ( const variable in data ) {
			form.append( variable, data[ variable ] );
		}
	}

	return fetch( Redirectioni10n.WP_API_root, {
		method: 'post',
		body: form,
		credentials: 'same-origin',
	} );
};

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
