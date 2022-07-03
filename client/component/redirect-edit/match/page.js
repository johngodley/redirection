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

const MatchPage = () => {
	return (
		<TableRow title={ __( 'Page Type', 'redirection' ) } className="redirect-edit__match">
			<p>
				{ __( 'Only the 404 page type is currently supported.', 'redirection' ) }&nbsp;
				{ __( 'Please do not try and redirect all your 404s - this is not a good thing to do.', 'redirection' ) }
			</p>
		</TableRow>
	);
};

MatchPage.propTypes = {
	data: PropTypes.object.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default MatchPage;
