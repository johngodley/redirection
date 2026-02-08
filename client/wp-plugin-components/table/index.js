/**
 * External dependencies
 */

import React from 'react';
import classnames from 'classnames';

/**
 * External dependencies
 */
import './style.scss';

const Table = ( { className, children } ) => {
	return (
		<table className={ classnames( 'wpl-table', className ) }>
			<tbody>{ children }</tbody>
		</table>
	);
};

export default Table;
