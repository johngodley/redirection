/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

const FailedRow = props => {
	const { headers } = props;

	return (
		<tbody>
			<tr>
				<td colSpan={ headers.length }>
					<p>{ __( 'Sorry, something went wrong loading the data - please try again' ) }</p>
				</td>
			</tr>
		</tbody>
	);
};

FailedRow.propTypes = {
	headers: PropTypes.array.isRequired,
};

export default FailedRow;
