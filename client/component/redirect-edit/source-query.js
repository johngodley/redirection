/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */

import Select from 'wp-plugin-components/select';
import TableRow from './table-row';
import { getSourceQuery } from './constants';

const RedirectSourceQuery = ( { query, regex, onChange } ) => {
	if ( regex ) {
		return null;
	}

	return (
		<TableRow title={ __( 'Query Parameters' ) } className="redirect-edit__sourcequery">
			<Select name="flag_query" items={ getSourceQuery() } value={ query } onChange={ onChange } />
		</TableRow>
	);
};

export default RedirectSourceQuery;
