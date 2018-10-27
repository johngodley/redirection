/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import NavigationPages from './navigation-pages';
import { STATUS_IN_PROGRESS } from 'state/settings/type';

class TableNav extends React.Component {
	static propTypes = {
		total: PropTypes.number.isRequired,
		selected: PropTypes.array.isRequired,
		table: PropTypes.object.isRequired,
		onAction: PropTypes.func.isRequired,
		onChangePage: PropTypes.func.isRequired,
		bulk: PropTypes.array,
		status: PropTypes.string.isRequired,
	};

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

		if ( parseInt( this.state.action, 10 ) !== -1 ) {
			this.props.onAction( this.state.action );
			this.setState( { action: -1 } );
		}
	}

	getBulk( bulk ) {
		const { selected } = this.props;

		return (
			<div className="alignleft actions bulkactions">
				<label htmlFor="bulk-action-selector-top" className="screen-reader-text">{ __( 'Select bulk action' ) }</label>

				<select name="action" id="bulk-action-selector-top" value={ this.state.action } disabled={ selected.length === 0 } onChange={ this.handleChange }>
					<option value="-1">{ __( 'Bulk Actions' ) }</option>

					{ bulk.map( item => <option key={ item.id } value={ item.id }>{ item.name }</option> ) }
				</select>

				<input type="submit" id="doaction" className="button action" value={ __( 'Apply' ) } disabled={ selected.length === 0 || parseInt( this.state.action, 10 ) === -1 } onClick={ this.handleClick } />
			</div>

		);
	}

	render() {
		const { total, table, bulk, status } = this.props;

		return (
			<div className="tablenav top">
				{ bulk && this.getBulk( bulk ) }

				{ this.props.children ? this.props.children : null }

				{ total > 0 && <NavigationPages per_page={ table.per_page } page={ table.page } total={ total } onChangePage={ this.props.onChangePage } inProgress= { status === STATUS_IN_PROGRESS } /> }
			</div>
		);
	}
}

export default TableNav;
