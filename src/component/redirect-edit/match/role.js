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

const MatchRole = ( { data, onChange } ) => {
	const { role } = data;

	return (
		<TableRow title={ __( 'Role', 'redirection' ) } className="redirect-edit__match">
			<input type="text" className="regular-text" value={ role } name="role" placeholder={ __( 'Enter role or capability value', 'redirection' ) } onChange={ onChange } />
		</TableRow>
	);
};

MatchRole.propTypes = {
	data: PropTypes.object.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default MatchRole;
