/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';
import Select from 'wp-plugin-components/select';

class TableGroup extends React.Component {
	constructor( props ) {
		super( props );

		this.state = { selected: props.selected };
	}

	onChange = ev => {
		this.setState( { selected: ev.target.value } );
	}

	onSubmit = () => {
		this.props.onGroup( this.state.selected );
	}

	render() {
		const { options, isEnabled } = this.props;

		return (
			<div className="alignleft actions">
				<Select items={ options } value={ this.state.selected } name="filter" onChange={ this.onChange } isEnabled={ isEnabled } />

				<button className="button" onClick={ this.onSubmit } disabled={ ! isEnabled }>{ __( 'Apply' ) }</button>
			</div>
		);
	}
}

export default TableGroup;
