import clsx from 'clsx';

interface Table {
	direction: string;
	orderBy: string;
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
	const { direction, orderBy } = table;

	const click = ( ev: React.MouseEvent< HTMLButtonElement > ) => {
		ev.preventDefault();
		onSetOrderBy( name, orderBy === name && direction === 'desc' ? 'asc' : 'desc' );
	};
	const classes = clsx( {
		'manage-column': true,
		sortable: true,
		asc: orderBy === name && direction === 'asc',
		desc: ( orderBy === name && direction === 'desc' ) || orderBy !== name,
		'column-primary': primary,
		[ 'column-' + name ]: true,
	} );

	const isActive = orderBy === name;
	const indicatorClass = clsx( 'sorting-indicator', {
		asc: isActive && direction === 'asc',
		desc: isActive && direction === 'desc',
	} );

	return (
		<th scope="col" className={ classes }>
			<button type="button" className="button-link" onClick={ click }>
				<span>{ title }</span>
				<span className={ indicatorClass } />
			</button>
		</th>
	);
};

export default SortableColumn;
