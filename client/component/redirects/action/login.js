/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

class ActionLogin extends React.Component {
	static propTypes = {
		logged_in: PropTypes.string.isRequired,
		logged_out: PropTypes.string.isRequired,
		onChange: PropTypes.func.isRequired,
	};

	onChange = ev => {
		this.props.onChange( 'login', ev.target.name, ev.target.value );
	}

	render() {
		return (
			<React.Fragment>
				<tr>
					<th>{ __( 'Logged In' ) }</th>
					<td>
						<input type="text" name="logged_in" value={ this.props.logged_in } onChange={ this.onChange } placeholder={ __( 'Target URL when matched' ) } />
					</td>
				</tr>
				<tr>
					<th>{ __( 'Logged Out' ) }</th>
					<td>
						<input type="text" name="logged_out" value={ this.props.logged_out } onChange={ this.onChange } placeholder={ __( 'Target URL when not matched' ) } />
					</td>
				</tr>
			</React.Fragment>
		);
	}
}

export default ActionLogin;
