/**
 * External dependencies
 */

import React from 'react';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import SortableColumn from './sortable-column';
import Column from './column';
import CheckColumn from './check-column';

const TableHeader = props => {
	const { isDisabled, onSetAllSelected, onSetOrderBy, isSelected, headers, table } = props;
	const setSelected = ev => {
		onSetAllSelected( ev.target.checked );
	};

	return (
		<tr>
			{ headers.map( item => {
				if ( item.check === true ) {
					return <CheckColumn onSetAllSelected={ setSelected } isDisabled={ isDisabled } isSelected={ isSelected } key={ item.name } />;
				}

				if ( item.sortable === false ) {
					return <Column name={ item.name } text={ item.title } key={ item.name } />;
				}

				return <SortableColumn table={ table } name={ item.name } text={ item.title } key={ item.name } onSetOrderBy={ onSetOrderBy } />;
			} ) }
		</tr>
	);
};

TableHeader.propTypes = {
	table: PropTypes.object.isRequired,
	isDisabled: PropTypes.bool.isRequired,
	isSelected: PropTypes.bool.isRequired,
	headers: PropTypes.array.isRequired,
	onSetAllSelected: PropTypes.func.isRequired,
	onSetOrderBy: PropTypes.func.isRequired,
};

export default TableHeader;
