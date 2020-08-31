/**
 * External dependencies
 */

import React from 'react';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

import { nestedGroups } from 'state/group/selector';
import { Select } from 'wp-plugin-components';

const RedirectGroup = ( { groups, currentGroup, onChange } ) => {
	return (
		<Select name="group" value={ currentGroup } items={ nestedGroups( groups ) } onChange={ onChange } />
	);
};

RedirectGroup.propTypes = {
	groups: PropTypes.array.isRequired,
	currentGroup: PropTypes.number.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default RedirectGroup;
