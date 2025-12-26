/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';
import PropTypes from 'prop-types';

const RedirectPosition = ( { position, onChange } ) => {
	return (
		<span className="redirect-edit-position">
			<strong>{ __( 'Position', 'redirection' ) }</strong>
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
