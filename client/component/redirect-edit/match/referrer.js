/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'wp-plugin-lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import ExternalLink from 'wp-plugin-components/external-link';
import TableRow from '../table-row';

const MatchReferrer = ( { data, onChange } ) => {
	const { referrer, regex } = data;

	return (
		<TableRow title={ __( 'Referrer' ) } className="redirect-edit__match">
			<input type="text" className="regular-text" name="referrer" value={ referrer } onChange={ onChange } placeholder={ __( 'Match against this browser referrer text' ) } />

			<label className="redirect-edit-regex">
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
