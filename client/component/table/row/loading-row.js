/**
 * External dependencies
 */

import React from 'react';

const Row = ( props ) => {
	const { columns } = props;

	return (
		<tr className="is-placeholder">
			{ columns.map( ( item, pos ) => (
				<td key={ pos }>
					<div className="placeholder-loading" />
				</td>
			) ) }

			<td>
				<div className="placeholder-loading" />
			</td>
		</tr>
	);
};

const LoadingRow = ( props ) => {
	const { headers, rows } = props;

	return (
		<>
			<Row columns={ headers } />

			{ rows.slice( 0, -1 ).map( ( item, pos ) => (
				<Row columns={ headers } key={ pos } />
			) ) }
		</>
	);
};

export default LoadingRow;
