/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import TableRow from '../table-row';

const MatchServer = ( { data, onChange } ) => {
	const { server } = data;

	return (
		<TableRow title={ __( 'Server' ) }>
			<input type="text" name="server" value={ server } placeholder={ __( 'Enter server URL to match against' ) } onChange={ onChange } />
		</TableRow>
	);
};

MatchServer.propTypes = {
	data: PropTypes.object.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default MatchServer;
