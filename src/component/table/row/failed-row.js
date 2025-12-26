/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';

const FailedRow = ( props ) => {
	const { headers } = props;

	return (
		<tr>
			<td colSpan={ headers.length + 1 }>
				<p>{ __( 'Sorry, something went wrong loading the data - please try again', 'redirection' ) }</p>
			</td>
		</tr>
	);
};

export default FailedRow;
