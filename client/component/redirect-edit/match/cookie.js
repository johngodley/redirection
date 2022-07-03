/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

import { ExternalLink } from 'wp-plugin-components';
import TableRow from '../table-row';

const MatchCookie = ( { data, onChange } ) => {
	const { name, value, regex } = data;

	return (
		<TableRow title={ __( 'Cookie', 'redirection' ) } className="redirect-edit__match">
			<input type="text" name="name" value={ name } onChange={ onChange } className="regular-text" placeholder={ __( 'Cookie name', 'redirection' ) } />
			<input type="text" name="value" value={ value } onChange={ onChange } className="regular-text" placeholder={ __( 'Cookie value', 'redirection' ) } />

			<label className="redirect-edit-regex">
				{ __( 'Regex', 'redirection' ) } <sup><ExternalLink url="https://redirection.me/support/redirect-regular-expressions/">?</ExternalLink></sup>
				&nbsp;
				<input type="checkbox" name="regex" checked={ regex } onChange={ onChange } />
			</label>
		</TableRow>
	);
};

MatchCookie.propTypes = {
	data: PropTypes.object.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default MatchCookie;
