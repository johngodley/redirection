/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

class MatchServer extends React.Component {
	static propTypes = {
		server: PropTypes.string.isRequired,
	};

	onChange = ev => {
		this.props.onChange( 'server', 'server', ev.target.value );
	}

	render() {
		return (
			<tr>
				<th>{ __( 'Server' ) }</th>
				<td>
					<input type="text" value={ this.props.server } placeholder={ __( 'Enter server URL to match against' ) } onChange={ this.onChange } />
				</td>
			</tr>
		);
	}
}

export default MatchServer;
