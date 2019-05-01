/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import ExternalLink from 'component/external-link';
import TableRow from '../table-row';

const MatchReferrer = ( { data, onChange } ) => {
	const { referrer, regex } = data;

	return (
		<TableRow title={ __( 'Referrer' ) }>
			<input type="text" name="referrer" value={ referrer } onChange={ onChange } placeholder={ __( 'Match against this browser referrer text' ) } />

			<label className="edit-redirection-regex">
				{ __( 'Regex' ) } <sup><ExternalLink url="https://redirection.me/support/redirect-regular-expressions/">?</ExternalLink></sup>
				&nbsp;

				<input type="checkbox" name="regex" checked={ regex } onChange={ onChange } />
			</label>
		</TableRow>
	);
};

MatchReferrer.propTypes = {
	data: PropTypes.object.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default MatchReferrer;
