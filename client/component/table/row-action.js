/**
 * External dependencies
 */

import React from 'react';
import PropTypes from 'prop-types';

const RowActions = props => {
	const { children, disabled = false } = props;

	return (
		<div className="row-actions">
			{ disabled ? <span>&nbsp;</span> : children }
		</div>
	);
};

RowActions.propTypes = {
	children: PropTypes.array.isRequired,
	disabled: PropTypes.bool,
};

export default RowActions;
