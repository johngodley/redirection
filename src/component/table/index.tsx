import clsx from 'clsx';
import TableHeader from './header';
import TableRows from './row';
import './style.scss';

type TableStatus = 'loading' | 'saving' | 'error' | 'complete';

interface TableHeaderItem {
	name: string;
	title: string;
	primary?: boolean;
	sortable?: boolean;
}

export interface Table {
	orderBy: string;
	direction: string;
	page: number;
	perPage: number;
	selected: number[];
	filterBy: { [ key: string ]: string };
	groupBy: string;
	displayType: string;
	displaySelected: string[];
}

interface TableRow {
	id: number;
	[ key: string ]: any;
}

interface RowData {
	isLoading: boolean;
	isSelected: boolean;
	table: Table;
	rowMode?: boolean;
	setRowMode?: ( mode: boolean ) => void;
}

export interface RenderedColumn {
	name: string;
	content: React.ReactNode;
	alwaysDisplay?: boolean;
}

export type { RowData, TableRow, TableStatus };

interface TableProps {
	table: Table;
	status: TableStatus;
	getRow: ( row: TableRow, rowData: RowData ) => RenderedColumn[] | React.ReactNode;
	getRowActions: ( row: TableRow, rowData: RowData ) => React.ReactNode;
	headers: TableHeaderItem[];
	rows: TableRow[];
	saving: number[];
	onSetOrderBy: ( orderBy: string, direction: string ) => void;
	onSelect?: ( ids: number[] ) => void;
}

const Table = ( props: TableProps ) => {
	const { headers, getRow, getRowActions, rows, table, status, saving, onSelect, onSetOrderBy } = props;

	const handleSelectAll = ( checked: boolean ) => {
		if ( onSelect ) {
			onSelect( checked ? rows.map( ( r ) => r.id ) : [] );
		}
	};

	const header = (
		<TableHeader
			table={ table }
			disabled={ status !== 'complete' || rows.length === 0 }
			headers={ headers }
			onSetOrderBy={ onSetOrderBy }
			onSelect={ handleSelectAll }
			hasBulk={ !! onSelect }
			allSelected={ table.selected.length === rows.length && rows.length !== 0 }
			isSaving={ saving.length > 0 }
		/>
	);

	const classes = clsx( 'wp-list-table', 'widefat', 'fixed', 'striped', 'items', {
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
					{ ...( onSelect ? { onSelect } : {} ) }
					saving={ saving }
				/>
			</tbody>

			<tfoot>{ header }</tfoot>
		</table>
	);
};

export default Table;
