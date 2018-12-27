/**
 * External dependencies
 */

import React from 'react';

/**
 * External dependencies
 */
import './style.scss';

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
