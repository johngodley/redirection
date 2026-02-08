/**
 * External dependencies
 */

import React from 'react';

const TableRow = ( props ) => {
	const { title, url = false } = props;

	return (
		<tr>
			<th>
				{ ! url && title }
				{ url && (
					<a href={ url } target="_blank">
						{ title }
					</a>
				) }
			</th>
			<td>{ props.children }</td>
		</tr>
	);
};

export default TableRow;
