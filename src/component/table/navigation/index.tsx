import clsx from 'clsx';
import NavigationPages from './navigation-pages';
import './style.scss';

type RowId = number | string;

interface Table {
	perPage: number;
	page: number;
	selected: RowId[];
	selectAll: boolean;
}

interface TableNavProps {
	total: number;
	rowCount: number;
	table: Table;
	children?: React.ReactNode;
	onChangePage: ( page: number ) => void;
	disabled: boolean;
	className: string;
	onSelectAll: ( selectAll: boolean ) => void;
}

function TableNav( props: TableNavProps ) {
	const { total, rowCount, table, children = null, onChangePage, disabled, className, onSelectAll } = props;

	return (
		<div className={ clsx( 'tablenav', className ) }>
			<div className="redirect-table__actions">{ children }</div>

			{ total > 0 && (
				<NavigationPages
					perPage={ table.perPage }
					page={ table.page }
					total={ total }
					rowCount={ rowCount }
					onChangePage={ onChangePage }
					onSelectAll={ onSelectAll }
					disabled={ disabled }
					selected={ table.selectAll ? total : table.selected.length }
					isEverything={ table.selectAll }
				/>
			) }
		</div>
	);
}

export default TableNav;
