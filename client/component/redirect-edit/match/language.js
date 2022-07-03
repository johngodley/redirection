/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import TableRow from '../table-row';

const MatchLanguage = ( { data, onChange } ) => {
	const { language } = data;

	return (
		<TableRow title={ __( 'Language', 'redirection' ) } className="redirect-edit__match">
			<input type="text" className="regular-text" name="language" value={ language } onChange={ onChange } placeholder={ __( 'Comma separated list of languages to match against (i.e. da, en-GB)', 'redirection' ) } />
		</TableRow>
	);
};

MatchLanguage.propTypes = {
	data: PropTypes.object.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default MatchLanguage;
