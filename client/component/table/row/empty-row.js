/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';

const EmptyRow = ( props ) => {
	const { headers } = props;

	return (
		<tr>
			<td />
			<td colSpan={ headers.length }>{ __( 'No results' ) }</td>
		</tr>
	);
};

export default EmptyRow;
