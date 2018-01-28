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
	constructor( props ) {
		super( props );

		this.handleChangeIn = this.onChangeIn.bind( this );
		this.handleChangeOut = this.onChangeOut.bind( this );
	}

	onChangeIn( ev ) {
		this.props.onChange( 'login', 'logged_in', ev.target.value );
	}

	onChangeOut( ev ) {
		this.props.onChange( 'login', 'logged_out', ev.target.value );
	}

	render() {
		return (
			<React.Fragment>
				<tr>
					<th>{ __( 'Logged In' ) }</th>
					<td>
						<input type="text" name="logged_in" value={ this.props.logged_in } onChange={ this.handleChangeIn } />
					</td>
				</tr>
				<tr>
					<th>{ __( 'Logged Out' ) }</th>
					<td>
						<input type="text" name="logged_out" value={ this.props.logged_out } onChange={ this.handleChangeOut } />
					</td>
				</tr>
			</React.Fragment>
		);
	}
}

ActionLogin.propTypes = {
	logged_in: PropTypes.string.isRequired,
	logged_out: PropTypes.string.isRequired,
};

export default ActionLogin;
