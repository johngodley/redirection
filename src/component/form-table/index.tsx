import './style.scss';

interface TableRowProps {
	title: React.ReactNode;
	url?: string | false;
	children: React.ReactNode;
}

export const TableRow = ( props: TableRowProps ) => {
	const { title, url = false, children } = props;

	return (
		<tr>
			<th>
				{ ! url && title }
				{ url && (
					<a href={ url } target="_blank" rel="noreferrer">
						{ title }
					</a>
				) }
			</th>
			<td>{ children }</td>
		</tr>
	);
};

interface FormTableProps {
	children: React.ReactNode;
}

export const FormTable = ( props: FormTableProps ) => {
	return (
		<table className="form-table">
			<tbody>{ props.children }</tbody>
		</table>
	);
};
