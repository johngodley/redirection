import clsx from 'clsx';
import NavigationPages from './navigation-pages';

interface Table {
	per_page: number;
	page: number;
	selected: number[];
	selectAll: boolean;
}

interface TableNavProps {
	total: number;
	table: Table;
	children?: React.ReactNode;
	onChangePage: ( page: number ) => void;
	disabled: boolean;
	className: string;
	onSelectAll: ( selectAll: boolean ) => void;
}

function TableNav( props: TableNavProps ) {
	const { total, table, children = null, onChangePage, disabled, className, onSelectAll } = props;

	return (
		<div className={ clsx( 'tablenav', className ) }>
			<div className="redirect-table__actions">{ children }</div>

			{ total > 0 && (
				<NavigationPages
					perPage={ table.per_page }
					page={ table.page }
					total={ total }
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
