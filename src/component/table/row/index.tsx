import { useState, memo, useCallback } from 'react';
import clsx from 'clsx';
import LoadingRow from './loading-row';
import EmptyRow from './empty-row';
import FailedRow from './failed-row';
import { Spinner } from '@wp-plugin-components';
import { STATUS_LOADING, STATUS_COMPLETE, STATUS_ERROR, STATUS_SAVING } from 'lib/constants';
import type { RenderedColumn, RowData, Table, TableRow, TableStatus, RowId } from '../index';

interface TableHeader {
	name: string;
	title: string;
	primary?: boolean;
}

function getRowData( status: TableStatus, item: TableRow, table: Table ): RowData {
	return {
		isLoading: status === STATUS_LOADING,
		isSelected: table.selected.some( ( id ) => id === item.id ),
		table,
	};
}

interface CheckColumnProps {
	id: RowId;
	onSelect: ( ids: RowId[] ) => void;
	isSelected: boolean;
	isSaving: boolean;
	disabled: boolean;
}

const CheckColumn = memo( function CheckColumn( props: CheckColumnProps ) {
	const { isSaving, disabled, id, onSelect, isSelected } = props;

	const handleChange = useCallback(
		( ev: React.ChangeEvent< HTMLInputElement > ) => {
			const value = ev.target.value;
			// Parse as number if it's numeric, otherwise keep as string
			const parsedId = /^\d+$/.test( value ) ? parseInt( value, 10 ) : value;
			onSelect( [ parsedId ] );
		},
		[ onSelect ]
	);

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
					checked={ isSelected }
					onChange={ handleChange }
				/>
			) }
		</th>
	);
} );

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

interface SingleRowProps {
	row: TableRow;
	status: TableStatus;
	isSelected: boolean;
	isSaving: boolean;
	isAllSaving: boolean;
	displaySelected: string[];
	table: Table;
	getRow: ( row: TableRow, rowData: RowData ) => RenderedColumn[] | React.ReactNode;
	getRowActions: ( row: TableRow, rowData: RowData ) => React.ReactNode;
	onSelect?: ( ids: RowId[] ) => void;
	primary: TableHeader | undefined;
	headersLength: number;
}

function SingleRowComponent( props: SingleRowProps ) {
	const {
		row,
		status,
		isSelected,
		isSaving,
		isAllSaving,
		displaySelected,
		table,
		getRow,
		getRowActions,
		onSelect,
		primary,
		headersLength,
	} = props;

	const rowData = getRowData( status, row, table );

	return (
		<tr
			className={ clsx( {
				disabled: status === STATUS_LOADING || status === STATUS_SAVING,
				saving: isSaving || isAllSaving,
			} ) }
		>
			{ !! onSelect && (
				<CheckColumn
					id={ row.id }
					onSelect={ onSelect }
					disabled={ status === STATUS_LOADING }
					isSelected={ isSelected }
					isSaving={ isSaving || isAllSaving }
				/>
			) }

			<RowColumns
				row={ row }
				rowData={ rowData }
				displaySelected={ displaySelected }
				getRow={ getRow }
				getRowActions={ getRowActions }
				primary={ primary }
				total={ headersLength }
			/>
		</tr>
	);
}

// Custom comparison to prevent re-renders when only selection changes on other rows
const SingleRow = memo( SingleRowComponent, ( prevProps, nextProps ) => {
	return (
		prevProps.row === nextProps.row &&
		prevProps.status === nextProps.status &&
		prevProps.isSelected === nextProps.isSelected &&
		prevProps.isSaving === nextProps.isSaving &&
		prevProps.isAllSaving === nextProps.isAllSaving &&
		prevProps.displaySelected === nextProps.displaySelected &&
		prevProps.primary === nextProps.primary &&
		prevProps.headersLength === nextProps.headersLength
	);
} );

interface TableRowsProps {
	rows: TableRow[];
	headers: TableHeader[];
	status: TableStatus;
	table: Table;
	getRow: ( row: TableRow, rowData: RowData ) => RenderedColumn[] | React.ReactNode;
	getRowActions: ( row: TableRow, rowData: RowData ) => React.ReactNode;
	onSelect?: ( ids: RowId[] ) => void;
	saving: RowId[];
}

function TableRows( props: TableRowsProps ) {
	const { rows, headers, status, table, getRow, getRowActions, onSelect, saving } = props;
	const { selected, displaySelected } = table;
	const primary = headers.find( ( item ) => item.primary );
	const isAllSaving = saving.some( ( id ) => id === -1 );

	if ( status === STATUS_LOADING && rows.length === 0 ) {
		return <LoadingRow headers={ headers } rows={ rows } />;
	}

	if ( status === STATUS_COMPLETE && rows.length === 0 ) {
		return <EmptyRow headers={ headers } />;
	}

	if ( status === STATUS_ERROR ) {
		return <FailedRow headers={ headers } />;
	}

	return (
		<>
			{ rows.map( ( row ) => (
				<SingleRow
					key={ row.id }
					row={ row }
					status={ status }
					isSelected={ selected.some( ( id ) => id === row.id ) }
					isSaving={ saving.some( ( id ) => id === row.id ) }
					isAllSaving={ isAllSaving }
					displaySelected={ displaySelected }
					table={ table }
					getRow={ getRow }
					getRowActions={ getRowActions }
					{ ...( onSelect ? { onSelect } : {} ) }
					primary={ primary }
					headersLength={ headers.length }
				/>
			) ) }
		</>
	);
}

export default TableRows;
