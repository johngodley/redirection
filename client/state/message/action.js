/* global Redirectioni10n */
/**
 * Internal dependencies
 */

import {
	MESSAGE_CLEAR_ERRORS,
	MESSAGE_CLEAR_NOTICES,
} from './type';
import getApi from 'lib/api';

export const clearErrors = () => ( { type: MESSAGE_CLEAR_ERRORS } );
export const clearNotices = () => ( { type: MESSAGE_CLEAR_NOTICES } );
export const ping = () => () => {
	getApi( 'red_ping' )
		.then( json => {
			Redirectioni10n.WP_API_nonce = json.nonce;
		} );
};
