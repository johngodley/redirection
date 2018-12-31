/**
 * External dependencies
 */

import React from 'react';
import PropTypes from 'prop-types';

const TableRow = ( { title = null, children, className = '' } ) => {
	return (
		<tr>
			<th className={ className }>{ title }</th>
			<td>
				{ children }
			</td>
		</tr>
	);
};

TableRow.propTypes = {
	title: PropTypes.string,
};

export default TableRow;
