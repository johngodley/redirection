import { __ } from '@wordpress/i18n';
import { ACTION_PASS, ACTION_NOTHING } from 'lib/redirect-constants';

interface Row {
	action_code: number;
	action_type: string;
}

function getCode( row: Row ): string | number {
	const { action_code, action_type } = row;

	if ( action_type === ACTION_PASS ) {
		return __( 'pass', 'redirection' );
	}

	if ( action_type === ACTION_NOTHING ) {
		return '-';
	}

	return action_code;
}

export default getCode;
