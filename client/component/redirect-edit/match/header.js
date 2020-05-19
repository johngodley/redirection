/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

import ExternalLink from 'component/external-link';
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
				<TableRow title={ __( 'HTTP Header' ) } className="redirect-edit__match">
					<input type="text" name="name" value={ name } onChange={ onChange } className="regular-text" placeholder={ __( 'Header name' ) } />
					<input type="text" name="value" value={ value } onChange={ onChange } className="regular-text" placeholder={ __( 'Header value' ) } />

					<select name="agent_dropdown" onChange={ this.onDropdown } value={ this.state.dropdown } className="medium">
						<option value="">{ __( 'Custom' ) }</option>
						<option value="accept">{ __( 'Accept Language' ) }</option>
					</select>

					<label className="redirect-edit-regex">
						{ __( 'Regex' ) } <sup><ExternalLink url="https://redirection.me/support/redirect-regular-expressions/">?</ExternalLink></sup>
						&nbsp;
						<input type="checkbox" name="regex" checked={ regex } onChange={ onChange } />
					</label>
				</TableRow>

				<TableRow>
					{ __( 'Note it is your responsibility to pass HTTP headers to PHP. Please contact your hosting provider for support about this.' ) }
				</TableRow>
			</>
		);
	}
}

export default MatchHeader;
