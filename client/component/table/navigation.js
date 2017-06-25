/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'lib/locale';

import NavigationPages from './navigation-pages';
import { performTableAction } from 'state/log/action';

class TableNav extends React.Component {
	constructor( props ) {
		super( props );

		this.handleClick = this.onClick.bind( this );
		this.handleChange = this.onChange.bind( this );

		this.state = { action: -1 };
	}

	onChange( ev ) {
		this.setState( { action: ev.target.value } );
	}

	onClick( ev ) {
		ev.preventDefault();

		if ( this.state.action !== '-1' ) {
			this.props.onTableAction( this.state.action );
			this.setState( { action: '-1' } );
		}
	}

	render() {
		const { total, selected } = this.props;

		return (
			<div className="tablenav top">
				<div className="alignleft actions bulkactions">
					<label htmlFor="bulk-action-selector-top" className="screen-reader-text">{ __( 'Select bulk action' ) }</label>

					<select name="action" id="bulk-action-selector-top" value={ this.state.action } disabled={ selected.length === 0 } onChange={ this.handleChange }>
						<option value="-1">{ __( 'Bulk Actions' ) }</option>
						<option value="delete">{ __( 'Delete' ) }</option>
					</select>

					<input type="submit" id="doaction" className="button action" value={ __( 'Apply' ) } disabled={ selected.length === 0 } onClick={ this.handleClick } />
				</div>

				{ total > 0 && <NavigationPages /> }
			</div>
		);
	}
}

function mapStateToProps( state ) {
	const { total, selected } = state.log;

	return {
		total,
		selected,
	};
}

function mapDispatchToProps( dispatch ) {
	return {
		onTableAction: action => {
			dispatch( performTableAction( action ) );
		}
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps,
)( TableNav );
