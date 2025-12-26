/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

import { ExternalLink } from '@wp-plugin-components';
import TableRow from '../table-row';

class MatchAgent extends React.Component {
	static propTypes = {
		data: PropTypes.object.isRequired,
		onChange: PropTypes.func.isRequired,
	};

	constructor( props ) {
		super( props );

		this.state = {
			dropdown: 0,
		};
	}

	onDropdown = ev => {
		const regex = {
			mobile: 'iPad|iPod|iPhone|Android|BlackBerry|SymbianOS|SCH-M\d+|Opera Mini|Windows CE|Nokia|SonyEricsson|webOS|PalmOS',
			feed: 'Bloglines|feed|rss',
			lib: 'cURL|Java|libwww-perl|PHP|urllib',
		};

		if ( ev.target.value !== '' ) {
			this.props.onChange( { target: { name: 'agent', value: regex[ ev.target.value ] } } );
		}

		this.setState( {
			dropdown: '',
		} );
	};

	render() {
		const { onChange, data } = this.props;
		const { agent, regex } = data;

		return (
			<TableRow title={ __( 'User Agent', 'redirection' ) } className="redirect-edit__match">
				<input type="text" name="agent" value={ agent } onChange={ onChange } className="regular-text" placeholder={ __( 'Match against this browser user agent', 'redirection' ) } />

				<select name="agent_dropdown" onChange={ this.onDropdown } value={ this.state.dropdown } className="medium">
					<option value="">{ __( 'Custom', 'redirection' ) }</option>
					<option value="mobile">{ __( 'Mobile', 'redirection' ) }</option>
					<option value="feed">{ __( 'Feed Readers', 'redirection' ) } </option>
					<option value="lib">{ __( 'Libraries', 'redirection' ) }</option>
				</select>

				<label className="redirect-edit-regex">
					{ __( 'Regex', 'redirection' ) } <sup><ExternalLink url="https://redirection.me/support/redirect-regular-expressions/">?</ExternalLink></sup>
					&nbsp;
					<input type="checkbox" name="regex" checked={ regex } onChange={ onChange } />
				</label>
			</TableRow>
		);
	}
}

export default MatchAgent;
