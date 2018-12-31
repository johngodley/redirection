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

const MatchCookie = ( { data, onChange } ) => {
	const { name, value, regex } = data;

	return (
		<TableRow title={ __( 'Cookie' ) }>
			<input type="text" name="name" value={ name } onChange={ onChange } className="medium" placeholder={ __( 'Cookie name' ) } />
			<input type="text" name="value" value={ value } onChange={ onChange } className="medium" placeholder={ __( 'Cookie value' ) } />

			<label className="edit-redirection-regex">
				{ __( 'Regex' ) } <sup><ExternalLink url="https://redirection.me/support/redirect-regular-expressions/">?</ExternalLink></sup>
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
