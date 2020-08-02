/**
 * External dependencies
 */

import React from 'react';

/**
 * Internal dependencies
 */
import SortableColumn from './sortable-column';
import Column from './column';
import CheckColumn from './check-column';

/** @typedef {import('../index.js').Table} Table */
/** @typedef {import('../index.js').TableHeader} TableHeader */
/** @typedef {import('../index.js').SetAllSelected} SetAllSelected */
/** @typedef {import('../index.js').SetOrderBy} SetOrderBy */

/**
 *
 * @param {object} props - Component props
 * @param {boolean} props.disabled - Is the table disabled?
 * @param {Table} props.table - Table params
 * @param {TableHeader[]} props.headers - Headers
 * @param {SetOrderBy} props.onSetOrderBy - When clicking on a sortable header
 * @param {SetAllSelected} props.onSetAllSelected - When clicking the 'set all'
 * @param {boolean} props.hasBulk - Has bulk actions
 * @param {boolean} props.allSelected - All items are selected
 */
const TableHeader = ( props ) => {
	const { disabled, onSetAllSelected, onSetOrderBy, headers, table, hasBulk, allSelected } = props;

	return (
		<tr>
			{ hasBulk && (
				<CheckColumn onSetAllSelected={ onSetAllSelected } disabled={ disabled } selected={ allSelected } />
			) }

			{ headers
				.map( ( item ) => {
					const { primary = false, sortable = true } = item;

					if ( sortable ) {
						return (
							<SortableColumn
								table={ table }
								name={ item.name }
								title={ item.title }
								key={ item.name }
								onSetOrderBy={ onSetOrderBy }
								primary={ primary }
							/>
						);
					}

					return <Column name={ item.name } title={ item.title } key={ item.name } primary={ primary } />;
				} ) }
		</tr>
	);
};

export default TableHeader;
