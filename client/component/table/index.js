/**
 * External dependencies
 */

import React from 'react';
import classnames from 'classnames';

/**
 * Internal dependencies
 */
import { STATUS_COMPLETE } from 'state/settings/type';

/**
 * Internal dependencies
 */
import TableHeader from './header';
import TableRows from './row';
import './style.scss';

/** @typedef {"asc"|"desc"} TableDirection */
/** @typedef {"STATUS_COMPLETE"|"STATUS_IN_PROGRESS"|"STATUS_FAILED"|"STATUS_SAVING"} TableStatus */

/**
 * @typedef TableHeader
 * @property {string} name - Name ID of column (to match the row column ID)
 * @property {string} title - Title of column
 * @property {boolean} [primary] - Whether the column is a primary column
 * @property {boolean} [sortable] - Whether the column is sortable
 */

/**
 * @typedef Table
 * @property {string} orderby
 * @property {TableDirection} direction
 * @property {number} page
 * @property {number} per_page
 * @property {number[]} selected
 * @property {object} filterBy
 * @property {string} groupBy
 * @property {string} displayType
 * @property {string[]} displaySelected
 */

/**
 * @typedef TableRow
 * @property {number} id
 */

/**
 * @typedef RowParams
 * @property {boolean} isLoading - Row is loading
 * @property {boolean} isSelected - Row is selected
 * @property {Table} table - Table data
 */

/**
 * @typedef RenderedRow
 * @property {string} name
 * @property {object|string} content
 */

/**
 * @callback GetRow
 * @param {object} row - Row data
 * @param {RowParams} rowParams - Row params
 * @returns {RenderedRow[]}
 */

/**
 * @callback SetAllSelected
 * @param {boolean} enabled - Enable or disable the checkboxes
 */

/**
 * @callback SetSelected
 * @param {number[]} items
 */

/**
 * @callback SetOrderBy
 * @param {string} orderBy
 * @param {string} direction
 */

/**
 * Display a table of rows
 *
 * @param {object} props - Component props
 * @param {Table} props.table
 * @param {TableStatus} props.status
 * @param {GetRow} props.getRow
 * @param {} props.getRowActions
 * @param {TableHeader[]} props.headers - Table headers
 * @param {TableRow[]} props.rows - Table rows
 * @param {number[]} props.saving
 * @param {SetOrderBy} props.onSetOrderBy
 * @param {SetSelected} [props.onSelect]
 */
const Table = ( props ) => {
	const {
		headers,
		getRow,
		getRowActions,
		rows,
		table,
		status,
		saving,
		onSelect,
		onSetOrderBy,
	} = props;

	const header = (
		<TableHeader
			table={ table }
			disabled={ status !== STATUS_COMPLETE || rows.length === 0 }
			headers={ headers }
			onSetOrderBy={ onSetOrderBy }
			onSelect={ onSelect }
			hasBulk={ !! onSelect }
			allSelected={ table.selected.length === rows.length && rows.length !== 0 }
			isSaving={ saving.length > 0 }
		/>
	);

	const classes = classnames( 'wp-list-table', 'widefat', 'fixed', 'striped', 'items', {
		[ 'redirect-log__display__' + table.displayType ]: true,
		[ 'redirect-log__group__' + table.groupBy ]: table.groupBy,
	} );

	return (
		<table className={ classes }>
			<thead>{ header }</thead>

			<tbody>
				<TableRows
					rows={ rows }
					table={ table }
					headers={ headers }
					status={ status }
					getRow={ getRow }
					getRowActions={ getRowActions }
					onSelect={ onSelect }
					saving={ saving }
				/>
			</tbody>

			<tfoot>{ header }</tfoot>
		</table>
	);
};

export default Table;
