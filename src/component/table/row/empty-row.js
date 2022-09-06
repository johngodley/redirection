/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';

const EmptyRow = ( props ) => {
	const { headers } = props;

	return (
		<tr>
			<td colSpan={ headers.length + 1 }>{ __( 'Nothing to display.', 'redirection' ) }</td>
		</tr>
	);
};

export default EmptyRow;
