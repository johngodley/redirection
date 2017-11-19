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
		props.onChange( 'target', 'url', ev.target.value );
	};

	return (
		<tr>
			<td colSpan="2" className="no-margin">
				<table>
					<tbody>
						<tr>
							<th>{ __( 'Target URL' ) }</th>
							<td>
								<input type="text" name="url" value={ props.target.url } onChange={ changer } />
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	);
};

ActionUrl.propTypes = {
	target: PropTypes.object.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default ActionUrl;
