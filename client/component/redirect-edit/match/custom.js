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

const MatchCustom = ( { data, onChange } ) => {
	const { filter } = data;

	return (
		<TableRow title={ __( 'Filter Name' ) } className="redirect-edit__match">
			<input type="text" name="filter" value={ filter } onChange={ onChange } className="regular-text" placeholder={ __( 'WordPress filter name' ) } />
		</TableRow>
	);
};

MatchCustom.propTypes = {
	data: PropTypes.object.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default MatchCustom;
