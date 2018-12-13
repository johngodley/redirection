/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

class ActionUrlFrom extends React.Component {
	static propTypes = {
		url_from: PropTypes.string.isRequired,
		url_notfrom: PropTypes.string.isRequired,
		target: PropTypes.string.isRequired,
	};

	onChange = ev => {
		this.props.onChange( this.props.target, ev.target.name, ev.target.value );
	}

	render() {
		return (
			<React.Fragment>
				<tr>
					<th>{ __( 'Matched Target' ) }</th>
					<td>
						<input type="text" name="url_from" value={ this.props.url_from } onChange={ this.onChange } placeholder={ __( 'Target URL when matched (empty to ignore)' ) } />
					</td>
				</tr>
				<tr>
					<th>{ __( 'Unmatched Target' ) }</th>
					<td>
						<input type="text" name="url_notfrom" value={ this.props.url_notfrom } onChange={ this.onChange } placeholder={ __( 'Target URL when not matched (empty to ignore)' ) } />
					</td>
				</tr>
			</React.Fragment>
		);
	}
}

export default ActionUrlFrom;
