/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

class MatchIp extends React.Component {
	static propTypes = {
		ip: PropTypes.array.isRequired,
	};

	onChange = ev => {
		this.props.onChange( 'ip', 'ip', ev.target.value.split( '\n' ) );
	}

	render() {
		return (
			<tr>
				<th className="top">{ __( 'IP' ) }</th>
				<td>
					<textarea value={ this.props.ip.join( '\n' ) } placeholder={ __( 'Enter IP addresses (one per line)' ) } onChange={ this.onChange } />
				</td>
			</tr>
		);
	}
}

export default MatchIp;
