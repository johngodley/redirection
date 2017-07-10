/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

const FailedRow = props => {
	const { headers, error } = props;

	return (
		<tbody>
			<tr>
				<td colSpan={ headers.length }>
					<p>{ __( 'Sorry but something went wrong loading the data - please try again' ) }</p>
					<p><em>{ error.message ? error.message : error }</em></p>
				</td>
			</tr>
		</tbody>
	);
};

FailedRow.propTypes = {
	headers: PropTypes.array.isRequired,
	error: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.object,
	] ).isRequired,
};

export default FailedRow;
