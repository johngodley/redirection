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
			<th>{ __( 'Target URL' ) }</th>
			<td>
				<input type="text" name="url" value={ props.target.url } onChange={ changer } placeholder={ __( 'The target URL you want to redirect to if matched' ) } />
			</td>
		</tr>
	);
};

ActionUrl.propTypes = {
	target: PropTypes.object.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default ActionUrl;
