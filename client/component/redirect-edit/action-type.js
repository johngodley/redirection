/**
 * External dependencies
 */

import React from 'react';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

import { Select } from 'wp-plugin-components';
import { getActions } from './constants';
import { hasUrlTarget, MATCH_LOGIN } from 'state/redirect/selector';

const ActionType = ( { actionType, matchType, onChange } ) => {
	const remover = item => {
		if ( matchType === MATCH_LOGIN && ! hasUrlTarget( item.value ) ) {
			return false;
		}

		return true;
	};

	return (
		<Select name="action_type" value={ actionType } items={ getActions().filter( remover ) } onChange={ onChange } />
	);
};

ActionType.propTypes = {
	actionType: PropTypes.string.isRequired,
	matchType: PropTypes.string.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default ActionType;
