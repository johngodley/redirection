/**
 * External dependencies
 */

import React from 'react';

/**
 * Internal dependencies
 */
import Table from 'component/table';
import TableNav from 'component/table/navigation';
import LogFilters from 'component/log-page/log-filters';
import BulkActions from 'component/table/bulk-actions';
import LogDisplay from './log-display';
import { STATUS_IN_PROGRESS } from 'state/settings/type';

/** @typedef {import('component/table').TableStatus} TableStatus */
/** @typedef {import('component/table').TableRow} TableRow */
/** @typedef {import('component/table').GetRow} GetRow */
/** @typedef {import('component/table').Table} Table */
/** @typedef {import('component/table').SetOrderBy} SetOrderBy */
/** @typedef {import('component/table').SetAllSelected} SetAllSelected */
/** @typedef {import('component/table').SetSelected} SetSelected */
/** @typedef {import('component/table').TableHeader} TableHeader */
/** @typedef {import('component/table/bulk-actions').BulkCallback} BulkCallback */
/** @typedef {import('component/table/bulk-actions').IdName} IdName */
/** @typedef {import('component/display-options').SetDisplayCallback} SetDisplayCallback */

/**
 * @callback ChangePageCallback
 * @param {number} page
 **/

/**
 * @callback FilterCallback
 * @param {object} filter
 **/

/**
 * @callback GroupCallback
 * @param {string} group
 **/

/**
 * @callback TableActions
 **/

/**
 * @typedef LabelValue
 * @property {string|typeof React} label
 * @property {string} value
 */

/**
 * @typedef LabelValueGrouping
 * @property {string|typeof React} label
 * @property {string} value
 * @property {string[]} grouping
 */

/**
 * @typedef LabelTitle
 * @property {string} label
 * @property {string} title
 */

/**
 * @typedef LogOptions
 * @property {LabelValue[]} displayFilters
 * @property {LabelValueGrouping[]} displayGroups
 * @property {LabelTitle[]} searchOptions
 * @property {LabelValue[]} groupBy
 * @property {LabelValue[]} rowFilters
 * @property {IdName[]} bulk
 * @property {TableHeader[]} props.headers - Table headers
 */

/**
 * @typedef LogActions
 * @property {ChangePageCallback} onChangePage
 * @property {FilterCallback} onFilter
 * @property {SetDisplayCallback} onSetDisplay
 * @property {BulkCallback} onBulk
 * @property {GroupCallback} onGroup
 * @property {SetOrderBy} onSetOrder
 * @property {SetAllSelected} onSetAll
 * @property {SetSelected} onSelect
 */

/**
 * Show a table of log entries
 *
 * @param {object} props - Component props
 * @param {LogOptions} props.logOptions - Options for the log table
 * @param {LogActions} props.logActions - Actions for the log table
 * @param {Table} props.table
 * @param {number} props.total - Total number of rows in the table
 * @param {TableRow[]} props.rows - Table rows
 * @param {TableStatus} props.status
 * @param {GetRow} props.getRow
 * @param {TableActions} [props.renderTableActions] - Render action buttons at the bottom of the table
 * @param {boolean} props.saving
 */
function LogPage( props ) {
	const {
		logOptions,
		logActions,
		table,
		total,
		rows,
		getRow,
		getRowActions,
		renderTableActions,
		status,
		saving,
	} = props;
	const {
		displayFilters,
		displayGroups,
		searchOptions,
		groupBy,
		rowFilters,
		bulk,
		headers,
		validateDisplay,
	} = logOptions;
	const { onChangePage, onFilter, onSetDisplay, onBulk, onGroup, onSetOrder, onSetAll, onSelect } = logActions;
	const disabled = status === STATUS_IN_PROGRESS;

	return (
		<>
			<LogDisplay
				filterOptions={ displayFilters }
				predefinedGroups={ displayGroups }
				searchOptions={ searchOptions }
				disabled={ disabled }
				table={ table }
				onFilter={ onFilter }
				onSetDisplay={ onSetDisplay }
				validateDisplay={ validateDisplay }
			/>

			<TableNav
				total={ total }
				table={ table }
				disabled={ disabled }
				onChangePage={ onChangePage }
				className="top"
				onSelectAll={ onSetAll }
			>
				<BulkActions bulk={ bulk } disabled={ disabled || table.selected.length === 0 } onBulk={ onBulk } />

				<LogFilters
					disabled={ disabled }
					table={ table }
					groupOptions={ groupBy }
					filterOptions={ rowFilters }
					onGroup={ onGroup }
					onFilter={ onFilter }
				/>
			</TableNav>

			<Table
				headers={ headers }
				rows={ rows }
				getRow={ getRow }
				getRowActions={ getRowActions }
				table={ table }
				status={ status }
				onSelect={ onSelect }
				onSetOrderBy={ onSetOrder }
				saving={ saving }
			/>

			<TableNav
				total={ total }
				table={ table }
				disabled={ disabled }
				onChangePage={ onChangePage }
				className="bottom"
				onSelectAll={ onSetAll }
			>
				<BulkActions bulk={ bulk } disabled={ disabled || table.selected.length === 0 } onBulk={ onBulk } />
				{ renderTableActions && renderTableActions() }
			</TableNav>
		</>
	);
}

export default LogPage;
