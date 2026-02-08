interface TableRowProps {
	title?: string | null;
	children: React.ReactNode;
	className?: string;
}

const TableRow = ( { title = null, children, className = '' }: TableRowProps ) => {
	return (
		<tr className={ className }>
			<th>{ title }</th>
			<td>{ children }</td>
		</tr>
	);
};

export default TableRow;
