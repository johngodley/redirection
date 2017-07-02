/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';

const FailedRow = props => {
	const { headers, error } = props;

	return (
		<tbody>
			<tr>
				<td />
				<td colSpan={ headers.length - 1 }>
					<p>{ __( 'Sorry but something went wrong loading the data - please try again' ) }</p>
					<p><em>{ error.message ? error.message : error }</em></p>
				</td>
			</tr>
		</tbody>
	);
};

export default FailedRow;
