import TableComponent, { type RowData, type RenderedColumn, type RowId } from 'component/table';
import TableNav from 'component/table/navigation';
import LogFilters from 'component/log-page/log-filters';
import BulkActions from 'component/table/bulk-actions';
import LogDisplay from './log-display';
import { STATUS_LOADING, type LoadingStatus } from 'lib/constants';

export type { RowData, RenderedColumn, RowId };

export interface LabelValue {
	label: string;
	value: string;
}

export interface LabelValueGrouping {
	label: string;
	value: string;
	grouping: string[];
}

export interface LabelTitle {
	name: string;
	title: string;
}

export interface IdName {
	id: string;
	name: string;
}

export interface TableHeader {
	name: string;
	title: string;
	primary?: boolean;
	sortable?: boolean;
}

export interface FilterBy {
	[ key: string ]: string;
}

export interface LogPageTable {
	page: number;
	perPage: number;
	orderBy: string;
	direction: string;
	selected: RowId[];
	selectAll: boolean;
	filter: string;
	filterBy: FilterBy;
	displayType: string;
	displaySelected: string[];
	groupBy: string;
}

export interface TableRow {
	id: number | string;
	[ key: string ]: unknown;
}

export type TableStatus = LoadingStatus;

export interface FilterOption {
	label: string;
	value: string;
}

export interface FilterGroup {
	label: string;
	value: string;
	options: FilterOption[];
}

export interface LogOptions {
	displayFilters: LabelValue[];
	displayGroups: LabelValueGrouping[];
	searchOptions: LabelTitle[];
	groupBy: LabelValue[];
	rowFilters: FilterGroup[];
	bulk: IdName[];
	headers: TableHeader[];
	validateDisplay?: ( selected: string[] ) => string[];
}

export interface LogActions {
	onChangePage: ( page: number ) => void;
	onFilter: ( filter: FilterBy ) => void;
	onSetDisplay: ( group: string, option: string[] ) => void;
	onBulk: ( action: string ) => void;
	onGroup: ( group: string ) => void;
	onSetOrder: ( orderBy: string, direction: string ) => void;
	onSetAll: ( checked: boolean ) => void;
	onSelect: ( id: RowId | RowId[] | boolean ) => void;
}

export interface LogPageProps {
	logOptions: LogOptions;
	logActions: LogActions;
	table: LogPageTable;
	total: number;
	rows: TableRow[];
	getRow: ( row: TableRow, rowParams: RowData ) => RenderedColumn[] | React.ReactNode;
	getRowActions: ( row: TableRow, rowParams: RowData ) => React.ReactNode;
	renderTableActions?: () => React.ReactElement;
	status: TableStatus;
	saving: RowId[];
}

function LogPage( props: LogPageProps ) {
	const { logOptions, logActions, table, total, rows, getRow, getRowActions, renderTableActions, status, saving } =
		props;
	const { displayFilters, displayGroups, searchOptions, groupBy, rowFilters, bulk, headers, validateDisplay } =
		logOptions;
	const { onChangePage, onFilter, onSetDisplay, onBulk, onGroup, onSetOrder, onSetAll, onSelect } = logActions;
	const disabled = status === STATUS_LOADING;

	const handleSelect = ( ids: RowId[] ) => {
		if ( ids.length === 0 ) {
			// Empty array means deselect all
			onSelect( false );
		} else if ( ids.length === 1 && ids[ 0 ] !== undefined ) {
			// Single item - toggle selection
			onSelect( ids[ 0 ] );
		} else {
			// Multiple items - select all (from header checkbox)
			onSelect( ids );
		}
	};

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
				{ ...( validateDisplay ? { validateDisplay } : {} ) }
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

			<TableComponent
				headers={ headers }
				rows={ rows }
				getRow={ getRow }
				getRowActions={ getRowActions }
				table={ table }
				status={ status }
				onSelect={ handleSelect }
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
