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

		this.state = {
			dropdown: 0,
		};
	}

	onChangeAgent( ev ) {
		this.props.onChange( 'agent', 'agent', ev.target.value );
	}

	onChangeRegex( ev ) {
		this.props.onChange( 'agent', 'regex', ev.target.checked );
	}

	onDropdown = ev => {
		const regex = {
			mobile: 'iPad|iPod|iPhone|Android|BlackBerry|SymbianOS|SCH-M\d+|Opera Mini|Windows CE|Nokia|SonyEricsson|webOS|PalmOS',
			feed: 'Bloglines|feed|rss',
			lib: 'cURL|Java|libwww-perl|PHP|urllib',
		};

		if ( ev.target.value !== '' ) {
			this.props.onChange( 'agent', 'agent', regex[ ev.target.value ] );
			this.props.onChange( 'agent', 'regex', true );
		}

		this.setState( {
			dropdown: ev.target.value,
		} );
	};

	render() {
		return (
			<tr>
				<th>{ __( 'User Agent' ) }</th>
				<td>
					<input type="text" name="agent" value={ this.props.agent } onChange={ this.handleChangeAgent } className="medium" /> &nbsp;

					<select name="agent_dropdown" onChange={ this.onDropdown } value={ this.state.dropdown } className="medium">
						<option value="">{ __( 'Custom' ) }</option>
						<option value="mobile">{ __( 'Mobile' ) }</option>
						<option value="feed">{ __( 'Feed Readers' ) } </option>
						<option value="lib">{ __( 'Libraries' ) }</option>
					</select>

					&nbsp; <label>
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
