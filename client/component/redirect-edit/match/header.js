/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

class MatchHeader extends React.Component {
	static propTypes = {
		name: PropTypes.string.isRequired,
		value: PropTypes.string.isRequired,
		regex: PropTypes.bool.isRequired,
		onChange: PropTypes.func.isRequired,
	};

	constructor( props ) {
		super( props );

		this.state = {
			dropdown: 0,
		};
	}

	onChange = ev => {
		this.props.onChange( 'header', ev.target.name, ev.target.value );
	}

	onChangeRegex = ev => {
		this.props.onChange( 'header', 'regex', ev.target.checked );
	}

	onDropdown = ev => {
		const regex = {
			accept: 'Accept-Language',
		};

		if ( ev.target.value !== '' ) {
			this.props.onChange( 'header', 'name', regex[ ev.target.value ] );
		}

		this.setState( {
			dropdown: '',
		} );
	};

	render() {
		const { name, value, regex } = this.props;

		return (
			<React.Fragment>
				<tr>
					<th>{ __( 'HTTP Header' ) }</th>
					<td className="custom-header-match">
						<input type="text" name="name" value={ name } onChange={ this.onChange } className="medium" placeholder={ __( 'Header name' ) } />
						<input type="text" name="value" value={ value } onChange={ this.onChange } className="medium" placeholder={ __( 'Header value' ) } />

						<select name="agent_dropdown" onChange={ this.onDropdown } value={ this.state.dropdown } className="medium">
							<option value="">{ __( 'Custom' ) }</option>
							<option value="accept">{ __( 'Accept Language' ) }</option>
						</select>

						<label className="edit-redirection-regex">
							{ __( 'Regex' ) } <sup><a tabIndex="-1" target="_blank" rel="noopener noreferrer" href="https://redirection.me/support/redirect-regular-expressions/">?</a></sup>
							&nbsp;
							<input type="checkbox" name="regex" checked={ regex } onChange={ this.onChangeRegex } />
						</label>
					</td>
				</tr>
				<tr>
					<th />
					<td>
						{ __( 'Note it is your responsibility to pass HTTP headers to PHP. Please contact your hosting provider for support about this.' ) }
					</td>
				</tr>
			</React.Fragment>
		);
	}
}

export default MatchHeader;
