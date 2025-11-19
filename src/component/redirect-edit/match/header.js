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

class MatchHeader extends React.Component {
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
		const headers = {
			accept: 'Accept-Language',
		};

		if ( ev.target.value !== '' ) {
			this.props.onChange( { target: { name: 'name', value: headers[ ev.target.value ] } } );
		}

		this.setState( {
			dropdown: '',
		} );
	};

	render() {
		const { onChange, data } = this.props;
		const { name, value, regex } = data;

		return (
			<>
				<TableRow title={ __( 'HTTP Header', 'redirection' ) } className="redirect-edit__match">
					<input type="text" name="name" value={ name } onChange={ onChange } className="regular-text" placeholder={ __( 'Header name', 'redirection' ) } />
					<input type="text" name="value" value={ value } onChange={ onChange } className="regular-text" placeholder={ __( 'Header value', 'redirection' ) } />

					<select name="agent_dropdown" onChange={ this.onDropdown } value={ this.state.dropdown } className="medium">
						<option value="">{ __( 'Custom', 'redirection' ) }</option>
						<option value="accept">{ __( 'Accept Language', 'redirection' ) }</option>
					</select>

					<label className="redirect-edit-regex">
						{ __( 'Regex', 'redirection' ) } <sup><ExternalLink url="https://redirection.me/support/redirect-regular-expressions/">?</ExternalLink></sup>
						&nbsp;
						<input type="checkbox" name="regex" checked={ regex } onChange={ onChange } />
					</label>
				</TableRow>

				<TableRow>
					{ __( 'Note it is your responsibility to pass HTTP headers to PHP. Please contact your hosting provider for support about this.', 'redirection' ) }
				</TableRow>
			</>
		);
	}
}

export default MatchHeader;
