/**
 * External dependencies
 */

import React from 'react';

export const TableRow = props => {
	const { title } = props;

	return (
		<tr>
			<th>{ title }</th>
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
