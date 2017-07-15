/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';
import Select from 'component/wordpress/select';

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
				<Select items={ options } value={ this.state.selected } name="filter" onChange={ this.handleChange } />

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
