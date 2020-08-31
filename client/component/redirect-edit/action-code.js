/**
 * External dependencies
 */

import React from 'react';
import PropTypes from 'prop-types';
import { translate as __ } from 'i18n-calypso';

/**
 * Internal dependencies
 */

import { Select } from 'wp-plugin-components';
import {
	ACTION_URL,
	ACTION_ERROR,
	ACTION_RANDOM,
} from 'state/redirect/selector';
import {
	getHttpError,
	getHttpCodes,
} from './constants';

function getCodes( actionType ) {
	if ( actionType === ACTION_ERROR ) {
		return getHttpError();
	}

	if ( actionType === ACTION_URL || actionType === ACTION_RANDOM ) {
		return getHttpCodes();
	}

	return null;
}

const ActionCode = ( { actionType, actionCode, onChange } ) => {
	const codes = getCodes( actionType );

	if ( codes ) {
		return (
			<>
				<strong className="small-flex">{ __( 'with HTTP code' ) }</strong>

				<Select name="action_code" value={ actionCode } items={ codes } onChange={ onChange } />
			</>
		);
	}

	return null;
};

ActionCode.propTypes = {
	actionType: PropTypes.string.isRequired,
	actionCode: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.number,
	] ).isRequired,
	onChange: PropTypes.func.isRequired,
};

export default ActionCode;
