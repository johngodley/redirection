/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

class TableFilter extends React.Component {
	constructor( props ) {
		super( props );
		this.state = { selected: props.selected };

		this.handleChange = this.onChange.bind( this );
		this.handleSubmit = this.onSubmit.bind( this );
	}

	onChange( ev ) {
		this.setState( { selected: ev.target.value } );
	}

	onSubmit() {
		this.props.onFilter( this.state.selected );
	}

	render() {
		const { options, isEnabled } = this.props;

		return (
			<div className="alignleft actions">
				<select value={ this.state.selected } onChange={ this.handleChange } disabled={ ! isEnabled }>
					{ options.map( item => <option key={ item.id } value={ item.id }>{ item.name }</option> ) }
				</select>

				<button className="button" onClick={ this.handleSubmit } disabled={ ! isEnabled }>{ __( 'Filter' ) }</button>
			</div>
		);
	}
}

TableFilter.propTypes = {
	options: PropTypes.array.isRequired,
	selected: PropTypes.string.isRequired,
	isEnabled: PropTypes.bool.isRequired,
	onFilter: PropTypes.func.isRequired,
};

export default TableFilter;
