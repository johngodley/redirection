/**
 * External dependencies
 */

import React from 'react';

export const TableRow = props => {
	const { title, url = false } = props;

	return (
		<tr>
			<th>
				{ ! url && title }
				{ url && <a href={ url } target="_blank">{ title }</a> }
			</th>
			<td>{ props.children }</td>
		</tr>
	);
};

export const FormTable = props => {
	return (
		<table className="form-table">
			<tbody>
				{ props.children }
			</tbody>
		</table>
	);
};
