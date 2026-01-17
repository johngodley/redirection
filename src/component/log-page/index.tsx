import TableComponent from 'component/table';
import TableNav from 'component/table/navigation';
import LogFilters from 'component/log-page/log-filters';
import BulkActions from 'component/table/bulk-actions';
import LogDisplay from './log-display';
import { STATUS_LOADING, type LoadingStatus } from 'lib/constants';

interface LabelValue {
	label: string;
	value: string;
}

interface LabelValueGrouping {
	label: string;
	value: string;
	grouping: string[];
}

interface LabelTitle {
	name: string;
	title: string;
}

interface IdName {
	id: string;
	name: string;
}

interface TableHeader {
	name: string;
	title: string;
	primary?: boolean;
	sortable?: boolean;
}

interface FilterBy {
	[ key: string ]: string;
}

interface Table {
	page: number;
	perPage: number;
	orderBy: string;
	direction: string;
	selected: number[];
	selectAll: boolean;
	filter: string;
	filterBy: FilterBy;
	displayType: string;
	displaySelected: string[];
	groupBy: string;
}

interface TableRow {
	id: number;
	[ key: string ]: any;
}

type TableStatus = LoadingStatus;

interface FilterGroup {
	label: string;
	value: string;
	options: Array< { label: string; value: string } >;
}

interface LogOptions {
	displayFilters: LabelValue[];
	displayGroups: LabelValueGrouping[];
	searchOptions: LabelTitle[];
	groupBy: LabelValue[];
	rowFilters: FilterGroup[];
	bulk: IdName[];
	headers: TableHeader[];
	validateDisplay?: ( selected: string[] ) => string[];
}

interface LogActions {
	onChangePage: ( page: number ) => void;
	onFilter: ( filter: FilterBy ) => void;
	onSetDisplay: ( group: string, option: string[] ) => void;
	onBulk: ( action: string ) => void;
	onGroup: ( group: string ) => void;
	onSetOrder: ( orderBy: string, direction: string ) => void;
	onSetAll: ( checked: boolean ) => void;
	onSelect: ( id: number | number[] | boolean ) => void;
}

interface LogPageProps {
	logOptions: LogOptions;
	logActions: LogActions;
	table: Table;
	total: number;
	rows: TableRow[];
	getRow: ( row: TableRow, rowParams: any ) => any;
	getRowActions: ( row: TableRow, rowParams: any ) => any;
	renderTableActions?: () => React.ReactElement;
	status: TableStatus;
	saving: number[];
}

function LogPage( props: LogPageProps ) {
	const { logOptions, logActions, table, total, rows, getRow, getRowActions, renderTableActions, status, saving } =
		props;
	const { displayFilters, displayGroups, searchOptions, groupBy, rowFilters, bulk, headers, validateDisplay } =
		logOptions;
	const { onChangePage, onFilter, onSetDisplay, onBulk, onGroup, onSetOrder, onSetAll, onSelect } = logActions;
	const disabled = status === STATUS_LOADING;

	const handleSelect = ( ids: number[] ) => {
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
