/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

const ActionFilter = props => {
	const changer = ev => {
		props.onChange( props.target, 'url', ev.target.value );
	};

	return (
		<tr>
			<th>{ __( 'Target URL' ) }</th>
			<td>
				<input type="text" name="url" value={ props.url } onChange={ changer } placeholder={ __( 'The target URL you want to redirect to if matched' ) } />
			</td>
		</tr>
	);
};

ActionFilter.propTypes = {
	url: PropTypes.string.isRequired,
	target: PropTypes.string.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default ActionFilter;
