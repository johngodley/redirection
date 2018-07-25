/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

class MatchCustom extends React.Component {
	static propTypes = {
		filter: PropTypes.string.isRequired,
		onChange: PropTypes.func.isRequired,
	};

	onChange = ev => {
		this.props.onChange( 'custom', 'filter', ev.target.value );
	}

	render() {
		return (
			<tr>
				<th>{ __( 'Filter Name' ) }</th>
				<td className="customfilter-match">
					<input type="text" name="filter" value={ this.props.filter } onChange={ this.onChange } className="medium" placeholder={ __( 'WordPress filter name' ) } />
				</td>
			</tr>
		);
	}
}

export default MatchCustom;
