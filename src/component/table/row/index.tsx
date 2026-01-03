import { useState } from 'react';
import clsx from 'clsx';
import LoadingRow from './loading-row';
import EmptyRow from './empty-row';
import FailedRow from './failed-row';
import { Spinner } from '@wp-plugin-components';
import type { RenderedColumn, RowData, Table, TableRow, TableStatus } from '../index';

interface TableHeader {
	name: string;
	title: string;
	primary?: boolean;
}

function getRowData( status: TableStatus, item: TableRow, table: Table ): RowData {
	return {
		isLoading: status === 'loading',
		isSelected: table.selected.includes( item.id ),
		table,
	};
}

interface CheckColumnProps {
	id: number;
	onSelect: ( ids: number[] ) => void;
	selected: number[];
	isSaving: boolean;
	disabled: boolean;
}

function CheckColumn( props: CheckColumnProps ) {
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
					checked={ selected.includes( `${ id }` as any ) || selected.includes( id ) }
					onChange={ ( ev ) => onSelect( [ parseInt( ev.target.value, 10 ) ] ) }
				/>
			) }
		</th>
	);
}

interface RowColumnsProps {
	row: TableRow;
	rowData: RowData;
	displaySelected: string[];
	getRow: ( row: TableRow, rowData: RowData ) => RenderedColumn[] | React.ReactNode;
	getRowActions: ( row: TableRow, rowData: RowData ) => React.ReactNode;
	primary: TableHeader | undefined;
	total: number;
}

function RowColumns( props: RowColumnsProps ) {
	const { row, rowData, displaySelected, getRow, getRowActions, primary, total } = props;
	const [ rowMode, setRowMode ] = useState( false );
	const data = { ...rowData, rowMode, setRowMode };
	const columns = getRow( row, data );
	const actions = getRowActions( row, data );

	if ( Array.isArray( columns ) ) {
		return (
			<>
				{ columns
					.filter( ( column ) => displaySelected.includes( column.name ) || column.alwaysDisplay === true )
					.map( ( column ) => (
						<td key={ column.name } className={ `column-${ column.name }` }>
							{ column.content }

							{ column.name === ( primary ? primary.name : columns[ 0 ]?.name ) && actions }
						</td>
					) ) }
			</>
		);
	}

	return <td colSpan={ total }>{ columns }</td>;
}

interface TableRowsProps {
	rows: TableRow[];
	headers: TableHeader[];
	status: TableStatus;
	table: Table;
	getRow: ( row: TableRow, rowData: RowData ) => RenderedColumn[] | React.ReactNode;
	getRowActions: ( row: TableRow, rowData: RowData ) => React.ReactNode;
	onSelect?: ( ids: number[] ) => void;
	saving: number[];
}

function TableRows( props: TableRowsProps ) {
	const { rows, headers, status, table, getRow, getRowActions, onSelect, saving } = props;
	const { selected, displaySelected } = table;
	const primary = headers.find( ( item ) => item.primary );

	if ( status === 'loading' && rows.length === 0 ) {
		return <LoadingRow headers={ headers } rows={ rows } />;
	}

	if ( status === 'complete' && rows.length === 0 ) {
		return <EmptyRow headers={ headers } />;
	}

	if ( status === 'error' ) {
		return <FailedRow headers={ headers } />;
	}

	return (
		<>
			{ rows.map( ( row ) => (
				<tr
					className={ clsx( {
						disabled: status === 'loading' || status === 'saving',
						saving: saving.includes( row.id ) || saving.includes( -1 ),
					} ) }
					key={ row.id }
				>
					{ !! onSelect && (
						<CheckColumn
							id={ row.id }
							onSelect={ onSelect }
							disabled={ status === 'loading' }
							selected={ selected }
							isSaving={ saving.includes( row.id ) || saving.includes( -1 ) }
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
			) ) }
		</>
	);
}

export default TableRows;
