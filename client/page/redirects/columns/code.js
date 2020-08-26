/**
 * External dependencies
 */

import { translate as __ } from 'i18n-calypso';

/**
 * Internal dependencies
 */
import { CODE_PASS, CODE_NOTHING } from 'state/redirect/selector';

function getCode( row ) {
	const { action_code, action_type } = row;

	if ( action_type === CODE_PASS ) {
		return __( 'pass' );
	}

	if ( action_type === CODE_NOTHING ) {
		return '-';
	}

	return action_code;
}

export default getCode;
