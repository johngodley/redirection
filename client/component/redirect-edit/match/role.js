/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

class MatchRole extends React.Component {
	onChange = ev => {
		if ( ev.target.value !== '' ) {
			this.props.onChange( 'role', 'role', ev.target.value );
		}
	}

	render() {
		return (
			<tr>
				<th>{ __( 'Role' ) }</th>
				<td>
					<input type="text" value={ this.props.role } placeholder={ __( 'Enter role or capability value' ) } onChange={ this.onChange } />
				</td>
			</tr>
		);
	}
}

MatchRole.propTypes = {
	role: PropTypes.string.isRequired,
};

export default MatchRole;
