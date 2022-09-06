/**
 * External dependencies
 */

import React, { useState } from 'react';
import classnames from 'classnames';

/**
 * Internal dependencies
 */
import LoadingRow from './loading-row';
import EmptyRow from './empty-row';
import FailedRow from './failed-row';
import { STATUS_IN_PROGRESS, STATUS_FAILED, STATUS_COMPLETE, STATUS_SAVING } from '../../../state/settings/type';
import { Spinner } from '@wp-plugin-components';

/** @typedef {import('../index.js').Table} Table */
/** @typedef {import('../index.js').TableStatus} TableStatus */
/** @typedef {import('../index.js').TableHeader} TableHeader */
/** @typedef {import('../index.js').GetRow} GetRow */
/** @typedef {import('../index.js').SetSelected} SetSelected */
/** @typedef {import('../index.js').TableRow} TableRow */

/**
 * Data for a row
 *
 * @param {TableStatus} status - Table status
 * @param {TableRow} item
 * @param {Table} table
 */
function getRowData( status, item, table ) {
	return {
		isLoading: status === STATUS_IN_PROGRESS,
		isSelected: table.selected.indexOf( item.id ) !== -1,
		table,
	};
}

/**
 * @param {object} props - Component props
 * @param {number} props.id - Row ID
 * @param {SetSelected} props.onSelect
 * @param {number[]} props.selected
 * @param {boolean} props.isSaving
 * @param {boolean} props.disabled
 */
function CheckColumn( props ) {
	const { isSaving, disabled, id, onSelect, selected } = props;

	return (
		<th scope="row" className="check-column">
			{ isSaving ? (
				<Spinner size="small" />
			) : (
				<input
					type="checkbox"
					name="item[]"
					value={ id }
					disabled={ disabled }
					checked={ selected.indexOf( `${ id }` ) !== -1 || selected.indexOf( id ) !== -1 }
					onChange={ ( ev ) => onSelect( [ ev.target.value ] ) }
				/>
			) }
		</th>
	);
}

function RowColumns( props ) {
	const { row, rowData, displaySelected, getRow, getRowActions, primary, total } = props;
	const [ rowMode, setRowMode ] = useState( false );
	const data = { ...rowData, rowMode, setRowMode };
	const columns = getRow( row, data );
	const actions = getRowActions( row, data );

	if ( Array.isArray( columns ) ) {
		return columns
			.filter( ( column ) => displaySelected.indexOf( column.name ) !== -1 || column.alwaysDisplay === true )
			.map( ( column ) => (
				<td key={ column.name } className={ `column-${ column.name }` }>
					{ column.content }

					{ column.name === ( primary ? primary.name : columns[ 0 ].name ) && actions }
				</td>
			) );
	}

	return <td colSpan={ total }>{ columns }</td>;
}

/**
 * Display table rows
 *
 * @param {object} props - Component props
 * @param {TableStatus} props.status - Table status
 * @param {Table} props.table - Table
 * @param {GetRow} props.getRow
 * @param {} props.getRowActions
 * @param {TableHeader[]} props.headers - Table headers
 * @param {TableRow[]} props.rows - Table rows
 * @param {boolean} props.hasBulk
 * @param {SetSelected} props.onSelect
 * @param {number[]} props.saving
 */
function TableRows( props ) {
	const { rows, headers, status, table, getRow, getRowActions, onSelect, saving } = props;
	const { selected, displaySelected } = table;
	const primary = headers.find( ( item ) => item.primary );

	if ( status === STATUS_IN_PROGRESS && rows.length === 0 ) {
		return <LoadingRow headers={ headers } rows={ rows } />;
	}

	if ( status === STATUS_COMPLETE && rows.length === 0 ) {
		return <EmptyRow headers={ headers } />;
	}

	if ( status === STATUS_FAILED ) {
		return <FailedRow headers={ headers } />;
	}

	// Render all the visible rows
	return rows.map( ( row ) => (
		<tr
			className={ classnames( {
				disabled: status === STATUS_IN_PROGRESS || status === STATUS_SAVING,
				saving: saving.indexOf( row.id ) !== -1 || saving.indexOf( -1 ) !== -1,
			} ) }
			key={ row.id }
		>
			{ !! onSelect && (
				<CheckColumn
					id={ row.id }
					onSelect={ onSelect }
					disabled={ status === STATUS_IN_PROGRESS }
					selected={ selected }
					isSaving={ saving.indexOf( row.id ) !== -1 || saving.indexOf( -1 ) !== -1 }
				/>
			) }

			<RowColumns
				row={ row }
				rowData={ getRowData( status, row, table ) }
				displaySelected={ displaySelected }
				getRow={ getRow }
				getRowActions={ getRowActions }
				primary={ primary }
				total={ headers.length }
			/>
		</tr>
	) );
}

export default TableRows;
