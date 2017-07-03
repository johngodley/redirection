/**
 * External dependencies
 */

import React from 'react';

const LoadingRow = props => {
	const { headers } = props;

	return (
		<tbody>
			<tr className="is-placeholder">
				<td><div className="placeholder-loading"></div></td>
				{ headers.map( ( item, pos ) => <td key={ pos }><div className="placeholder-loading"></div></td> ) }
			</tr>
		</tbody>
	);
};

export default LoadingRow;
