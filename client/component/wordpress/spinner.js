/**
 * External dependencies
 */

import React from 'react';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

import './spinner.scss';

const Spinner = props => {
	const { size = '' } = props;
	const klasses = 'spinner-container' + ( size ? ' spinner-' + size : '' );

	return (
		<div className={ klasses }>
			<span className="css-spinner" />
		</div>
	);
};

Spinner.propTypes = {
	size: PropTypes.string,
};

export default Spinner;
