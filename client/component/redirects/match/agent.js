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
			this.props.onCustomAgent( regex[ ev.target.value ] );
		}

		this.setState( {
			dropdown: '',
		} );
	};

	render() {
		return (
			<tr>
				<th>{ __( 'User Agent' ) }</th>
				<td className="useragent-match">
					<input type="text" name="agent" value={ this.props.agent } onChange={ this.handleChangeAgent } className="medium" />

					<select name="agent_dropdown" onChange={ this.onDropdown } value={ this.state.dropdown } className="medium">
						<option value="">{ __( 'Custom' ) }</option>
						<option value="mobile">{ __( 'Mobile' ) }</option>
						<option value="feed">{ __( 'Feed Readers' ) } </option>
						<option value="lib">{ __( 'Libraries' ) }</option>
					</select>

					<label className="edit-redirection-regex">
						{ __( 'Regex' ) } <sup><a tabIndex="-1" target="_blank" rel="noopener noreferrer" href="https://redirection.me/support/redirect-regular-expressions/">?</a></sup>
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
