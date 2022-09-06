/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */

import { Select } from '@wp-plugin-components';
import TableRow from './table-row';
import { getSourceQuery } from './constants';

const RedirectSourceQuery = ( { query, regex, onChange, url } ) => {
	if ( regex ) {
		return null;
	}

	const items =
		url.indexOf( '?' ) === -1 ? getSourceQuery().filter( ( item ) => item.value !== 'exactorder' ) : getSourceQuery();

	return (
		<TableRow title={ __( 'Query Parameters', 'redirection' ) } className="redirect-edit__sourcequery">
			<Select name="flag_query" items={ items } value={ query } onChange={ onChange } />
		</TableRow>
	);
};

export default RedirectSourceQuery;
