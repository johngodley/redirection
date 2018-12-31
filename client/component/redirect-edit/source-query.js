/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

import Select from 'component/select';
import TableRow from './table-row';
import { getSourceQuery } from './constants';

const RedirectSourceQuery = ( { query, regex, onChange } ) => {
	if ( regex ) {
		return null;
	}

	return (
		<TableRow title={ __( 'Query Parameters' ) }>
			<Select name="flag_query" items={ getSourceQuery() } value={ query } onChange={ onChange } />
		</TableRow>
	);
};

RedirectSourceQuery.propTypes = {
	query: PropTypes.string.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default RedirectSourceQuery;
