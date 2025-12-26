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

const MatchServer = ( { data, onChange } ) => {
	const { server } = data;

	return (
		<TableRow title={ __( 'Server', 'redirection' ) } className="redirect-edit__match">
			<input type="text" className="regular-text" name="server" value={ server } placeholder={ __( 'Enter server URL to match against', 'redirection' ) } onChange={ onChange } />
		</TableRow>
	);
};

MatchServer.propTypes = {
	data: PropTypes.object.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default MatchServer;
