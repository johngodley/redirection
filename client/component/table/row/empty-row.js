/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';

const EmptyRow = props => {
	const { headers } = props;

	return (
		<tbody>
			<tr>
				<td />
				<td colSpan={ headers.length - 1 }>{ __( 'No results' ) }</td>
			</tr>
		</tbody>
	);
};

export default EmptyRow;
