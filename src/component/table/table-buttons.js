/**
 * External dependencies
 */

import React from 'react';

const TableButtons = props => {
	const { enabled = true, children } = props;

	if ( enabled ) {
		return (
			<div className="table-buttons">
				{ children }
			</div>
		);
	}

	return null;
};

export default TableButtons;
