/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';
import Select from 'component/select';

class TableGroup extends React.Component {
	static propTypes = {
		options: PropTypes.array.isRequired,
		selected: PropTypes.string.isRequired,
		isEnabled: PropTypes.bool.isRequired,
		onGroup: PropTypes.func.isRequired,
	};

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

				<button className="button" onClick={ this.onSubmit } disabled={ ! isEnabled }>{ __( 'Group' ) }</button>
			</div>
		);
	}
}

export default TableGroup;
