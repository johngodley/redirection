import clsx from 'clsx';

interface ColumnProps {
	name: string;
	title: string;
	primary?: boolean;
}

const Column = ( props: ColumnProps ) => {
	const { name, title, primary } = props;
	const classes = clsx( {
		'manage-column': true,
		'column-primary': primary,
		[ 'column-' + name ]: true,
	} );

	return (
		<th scope="col" className={ classes }>
			<span>{ title }</span>
		</th>
	);
};

export default Column;
