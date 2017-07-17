/**
 * External dependencies
 */

import React from 'react';
import PropTypes from 'prop-types';

const Row = props => {
	const { columns } = props;

	return (
		<tr className="is-placeholder">
			{ columns.map( ( item, pos ) => <td key={ pos }><div className="placeholder-loading"></div></td> ) }
		</tr>
	);
};

const LoadingRow = props => {
	const { headers, rows } = props;

	return (
		<tbody>
			<Row columns={ headers } />

			{ rows.slice( 0, -1 ).map( ( item, pos ) => <Row columns={ headers } key={ pos } /> ) }
		</tbody>
	);
};

LoadingRow.propTypes = {
	headers: PropTypes.array.isRequired,
	rows: PropTypes.array.isRequired,
};

export default LoadingRow;
