/**
 * External dependencies
 */

import React from 'react';

/** @typedef {import('component/table').TableRow} TableRow */
/** @typedef {import('../../page/logs404/404-columns.js').Row404} Row404 */

/**
 * @param {TableRow & Row404} row
 */
export default function ColumnDate( row ) {
	const { created, created_time } = row;

	return (
		<>
			{ created }
			<br />
			{ created_time }
		</>
	)
}
