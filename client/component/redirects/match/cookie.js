/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

class MatchCookue extends React.Component {
	static propTypes = {
		name: PropTypes.string.isRequired,
		value: PropTypes.string.isRequired,
		regex: PropTypes.bool.isRequired,
		onChange: PropTypes.func.isRequired,
	};

	constructor( props ) {
		super( props );
	}

	onChange = ev => {
		this.props.onChange( 'cookie', ev.target.name, ev.target.value );
	}

	onChangeRegex = ev => {
		this.props.onChange( 'cookie', 'regex', ev.target.checked );
	}

	render() {
		const { name, value, regex } = this.props;

		return (
			<tr>
				<th>{ __( 'Cookie' ) }</th>
				<td className="custom-header-match">
					<input type="text" name="name" value={ name } onChange={ this.onChange } className="medium" placeholder={ __( 'Cookie name' ) } />
					<input type="text" name="value" value={ value } onChange={ this.onChange } className="medium" placeholder={ __( 'Cookie value' ) } />

					<label className="edit-redirection-regex">
						{ __( 'Regex' ) } <sup><a tabIndex="-1" target="_blank" rel="noopener noreferrer" href="https://redirection.me/support/redirect-regular-expressions/">?</a></sup>
						&nbsp;
						<input type="checkbox" name="regex" checked={ regex } onChange={ this.onChangeRegex } />
					</label>
				</td>
			</tr>
		);
	}
}

export default MatchCookue;
