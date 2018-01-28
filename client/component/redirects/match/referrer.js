/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

class MatchReferrer extends React.Component {
	constructor( props ) {
		super( props );

		this.handleChangeReferrer = this.onChangeReferrer.bind( this );
		this.handleChangeRegex = this.onChangeRegex.bind( this );
	}

	onChangeReferrer( ev ) {
		this.props.onChange( 'referrer', 'referrer', ev.target.value );
	}

	onChangeRegex( ev ) {
		this.props.onChange( 'referrer', 'regex', ev.target.checked );
	}

	render() {
		return (
			<tr>
				<th>{ __( 'Referrer' ) }</th>
				<td>
					<input type="text" name="referrer" value={ this.props.referrer } onChange={ this.handleChangeReferrer } />
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

MatchReferrer.propTypes = {
	referrer: PropTypes.string.isRequired,
	regex: PropTypes.bool.isRequired,
};

export default MatchReferrer;
