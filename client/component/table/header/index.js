/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'lib/locale';

import SortableColumn from './sortable-column';
import Column from './column';
import { setAllSelected } from 'state/log/action';

const CheckColumn = props => {
	const { onSetAllSelected, isDisabled, isSelected } = props;

	return (
		<td className="manage-column column-cb column-check" onClick={ onSetAllSelected }>
			<label className="screen-reader-text">{ __( 'Select All' ) }</label>
			<input type="checkbox" disabled={ isDisabled } checked={ isSelected } />
		</td>
	);
};

const TableHeader = props => {
	const { orderBy, direction, isDisabled, onSetAllSelected, isSelected, headers } = props;

	return (
		<tr>
			{ headers.map( item => {
				if ( item.check === true ) {
					return <CheckColumn onSetAllSelected={ onSetAllSelected } isDisabled={ isDisabled } isSelected={ isSelected } key={ item.name } />;
				}

				if ( item.sortable === false ) {
					return <Column name={ item.name } text={ item.title } key={ item.name } />;
				}

				return <SortableColumn orderBy={ orderBy } direction={ direction } name={ item.name } text={ item.title } key={ item.name } />;
			} ) }
		</tr>
	);
};

function mapStateToProps( state ) {
	const { orderBy, direction } = state.log;

	return {
		orderBy,
		direction,
	};
}

function mapDispatchToProps( dispatch ) {
	return {
		onSetAllSelected: ev => {
			dispatch( setAllSelected( ev.target.checked ) );
		}
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps,
)( TableHeader );
