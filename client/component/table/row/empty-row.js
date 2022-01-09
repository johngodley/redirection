/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';

const EmptyRow = ( props ) => {
	const { headers } = props;

	return (
		<tr>
			<td colSpan={ headers.length }>{ __( 'Nothing to display.' ) }</td>
		</tr>
	);
};

export default EmptyRow;
