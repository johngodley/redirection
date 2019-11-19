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

const ActionLogin = ( { onChange, data } ) => {
	const { logged_in, logged_out } = data;

	return (
		<React.Fragment>
			<TableRow title={ __( 'Logged In' ) } className="redirect-edit__target__matched">
				<input type="text" className="regular-text" name="logged_in" value={ logged_in } onChange={ onChange } placeholder={ __( 'Target URL when matched (empty to ignore)' ) } />
			</TableRow>
			<TableRow title={ __( 'Logged Out' ) } className="redirect-edit__target__unmatched">
				<input type="text" className="regular-text" name="logged_out" value={ logged_out } onChange={ onChange } placeholder={ __( 'Target URL when not matched (empty to ignore)' ) } />
			</TableRow>
		</React.Fragment>
	);
};

ActionLogin.propTypes = {
	data: PropTypes.object.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default ActionLogin;
