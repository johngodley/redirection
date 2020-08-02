/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'wp-plugin-lib/locale';

const FailedRow = ( props ) => {
	const { headers } = props;

	return (
		<tr>
			<td colSpan={ headers.length + 1 }>
				<p>{ __( 'Sorry, something went wrong loading the data - please try again' ) }</p>
			</td>
		</tr>
	);
};

export default FailedRow;
