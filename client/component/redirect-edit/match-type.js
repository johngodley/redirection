/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

import Select from 'wp-plugin-components/select';
import TableRow from './table-row';
import { getMatches } from './constants';

const MatchType = ( { matchType, onChange } ) => {
	return (
		<TableRow title={ __( 'Match' ) }>
			<Select name="match_type" value={ matchType } items={ getMatches() } onChange={ onChange } />
		</TableRow>
	);
};

MatchType.propTypes = {
	matchType: PropTypes.string.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default MatchType;
