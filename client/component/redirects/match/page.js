/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

class MatchPage extends React.Component {
	static propTypes = {
		page: PropTypes.string.isRequired,
	};

	onChange = ev => {
		this.props.onChange( 'page', 'page', ev.target.value );
	}

	render() {
		return (
			<tr>
				<th>{ __( 'Page Type' ) }</th>
				<td>
					{ __( 'Only the 404 page type is currently supported.' ) }&nbsp;
					{ __( 'Please do not try and redirect all your 404s - this is not a good thing to do.' ) }
				</td>
			</tr>
		);
	}
}

export default MatchPage;
