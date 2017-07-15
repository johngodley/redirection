/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

class MatchAgent extends React.Component {
	constructor( props ) {
		super( props );

		this.handleChangeAgent = this.onChangeAgent.bind( this );
		this.handleChangeRegex = this.onChangeRegex.bind( this );
	}

	onChangeAgent( ev ) {
		this.props.onChange( 'agent', 'agent', ev.target.value );
	}

	onChangeRegex( ev ) {
		this.props.onChange( 'agent', 'regex', ev.target.checked );
	}

	render() {
		return (
			<tr>
				<th>{ __( 'User Agent' ) }</th>
				<td>
					<input type="text" name="agent" value={ this.props.agent } onChange={ this.handleChangeAgent } /> &nbsp;
					<label>
						{ __( 'Regex' ) }
						&nbsp;
						<input type="checkbox" name="regex" checked={ this.props.regex } onChange={ this.handleChangeRegex } />
					</label>
				</td>
			</tr>
		);
	}
}

MatchAgent.propTypes = {
	agent: PropTypes.string.isRequired,
	regex: PropTypes.bool.isRequired,
};

export default MatchAgent;
