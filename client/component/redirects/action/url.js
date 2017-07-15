/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

const ActionUrl = props => {
	const changer = ev => {
		props.onChange( 'target', ev.target.value );
	};

	return (
		<tr>
			<td colSpan="2" className="no-margin">
				<table>
					<tbody>
						<tr>
							<th>{ __( 'Target URL' ) }</th>
							<td>
								<input type="text" name="action_data" value={ props.target } onChange={ changer } />
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	);
};

ActionUrl.propTypes = {
	target: PropTypes.string.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default ActionUrl;
