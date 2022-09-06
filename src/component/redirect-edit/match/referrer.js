/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { ExternalLink } from '@wp-plugin-components';
import TableRow from '../table-row';

const MatchReferrer = ( { data, onChange } ) => {
	const { referrer, regex } = data;

	return (
		<TableRow title={ __( 'Referrer', 'redirection' ) } className="redirect-edit__match">
			<input type="text" className="regular-text" name="referrer" value={ referrer } onChange={ onChange } placeholder={ __( 'Match against this browser referrer text', 'redirection' ) } />

			<label className="redirect-edit-regex">
				{ __( 'Regex', 'redirection' ) } <sup><ExternalLink url="https://redirection.me/support/redirect-regular-expressions/">?</ExternalLink></sup>
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
