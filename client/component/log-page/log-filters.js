/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */

import BulkAction from 'component/table/bulk-action';
import MultiOptionDropdown from 'component/multi-option-dropdown';
import TableGroup from 'component/table/group';

/** @typedef {import('component/table').Table} Table */
/** @typedef {import('./index.js').GroupCallback} GroupCallback */
/** @typedef {import('./index.js').FilterCallback} FilterCallback */

/**
 *
 * @param {object} props Component props
 * @param {Table} props.table
 * @param {boolean} props.disabled
 * @param {} props.groupOptions
 * @param {} props.filterOptions
 * @param {GroupCallback} props.onGroup
 * @param {FilterCallback} props.onFilter
 */
function LogFilters( props ) {
	const { table, disabled, groupOptions, filterOptions, onGroup, onFilter } = props;

	return (
		<>
			{ groupOptions.length > 0 && (
				<TableGroup
					selected={ table.groupBy ? table.groupBy : '0' }
					options={ groupOptions }
					isEnabled={ ! disabled }
					onGroup={ onGroup }
					key={ table.groupBy }
				/>
			) }

			{ filterOptions.length > 0 && (
				<BulkAction>
					<MultiOptionDropdown
						options={ filterOptions }
						selected={ table.filterBy ? table.filterBy : {} }
						onApply={ onFilter }
						title={ __( 'Filters' ) }
						isEnabled={ ! disabled }
						badges
					/>
				</BulkAction>
			) }
		</>
	);
}

export default LogFilters;
