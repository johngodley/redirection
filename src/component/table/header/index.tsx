import SortableColumn from './sortable-column';
import Column from './column';
import CheckColumn from './check-column';

interface Table {
	direction: string;
	orderby: string;
	selected: number[];
}

interface TableHeaderItem {
	name: string;
	title: string;
	primary?: boolean;
	sortable?: boolean;
}

interface TableHeaderProps {
	disabled: boolean;
	onSelect: ( checked: boolean ) => void;
	onSetOrderBy: ( orderBy: string, direction: string ) => void;
	headers: TableHeaderItem[];
	table: Table;
	hasBulk: boolean;
	allSelected: boolean;
	isSaving: boolean;
}

const TableHeader = ( props: TableHeaderProps ) => {
	const { disabled, onSelect, onSetOrderBy, headers, table, hasBulk, allSelected, isSaving } = props;

	return (
		<tr>
			{ hasBulk && (
				<CheckColumn onSelect={ onSelect } disabled={ disabled || isSaving } selected={ allSelected } />
			) }

			{ headers.map( ( item ) => {
				const { primary = false, sortable = true } = item;

				if ( sortable ) {
					return (
						<SortableColumn
							table={ table }
							name={ item.name }
							title={ item.title }
							key={ item.name }
							onSetOrderBy={ onSetOrderBy }
							primary={ primary }
						/>
					);
				}

				return <Column name={ item.name } title={ item.title } key={ item.name } primary={ primary } />;
			} ) }
		</tr>
	);
};

export default TableHeader;
