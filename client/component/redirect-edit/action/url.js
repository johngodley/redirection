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

const ActionUrl = ( { onChange, data } ) => {
	const { url } = data;

	return (
		<TableRow title={ __( 'Target URL' ) }>
			<input type="text" name="url" value={ url } onChange={ onChange } placeholder={ __( 'The target URL you want to redirect to if matched' ) } />
		</TableRow>
	);
};

ActionUrl.propTypes = {
	data: PropTypes.object.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default ActionUrl;
