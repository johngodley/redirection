import clsx from 'clsx';

interface Table {
	direction: string;
	orderby: string;
}

interface SortableColumnProps {
	name: string;
	title: string;
	table: Table;
	primary?: boolean;
	onSetOrderBy: ( orderBy: string, direction: string ) => void;
}

const SortableColumn = ( props: SortableColumnProps ) => {
	const { name, title, table, primary, onSetOrderBy } = props;
	const { direction, orderby } = table;

	const click = ( ev: React.MouseEvent< HTMLButtonElement > ) => {
		ev.preventDefault();
		onSetOrderBy( name, orderby === name && direction === 'desc' ? 'asc' : 'desc' );
	};
	const classes = clsx( {
		'manage-column': true,
		sortable: true,
		asc: orderby === name && direction === 'asc',
		desc: ( orderby === name && direction === 'desc' ) || orderby !== name,
		'column-primary': primary,
		[ 'column-' + name ]: true,
	} );

	return (
		<th scope="col" className={ classes }>
			<button type="button" className="button-link" onClick={ click }>
				<span>{ title }</span>
				<span className="sorting-indicator" />
			</button>
		</th>
	);
};

export default SortableColumn;
