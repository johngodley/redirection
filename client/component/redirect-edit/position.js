/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

const RedirectPosition = ( { position, onChange } ) => {
	return (
		<span className="redirect-edit-position">
			<strong>{ __( 'Position' ) }</strong>
			&nbsp;<input type="number" value={ position } name="position" min="0" size="3" onChange={ onChange } />
		</span>
	);
};

RedirectPosition.propTypes = {
	position: PropTypes.oneOfType( [
		PropTypes.number,
		PropTypes.string,
	] ).isRequired,
	onChange: PropTypes.func.isRequired,
};

export default RedirectPosition;
