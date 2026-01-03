interface Header {
	name: string;
	title: string;
}

interface RowProps {
	columns: Header[];
}

const Row = ( props: RowProps ) => {
	const { columns } = props;

	return (
		<tr className="is-placeholder">
			{ columns.map( ( _item, pos ) => (
				<td key={ pos }>
					<div className="wpl-placeholder__loading" />
				</td>
			) ) }

			<td>
				<div className="wpl-placeholder__loading" />
			</td>
		</tr>
	);
};

interface LoadingRowProps {
	headers: Header[];
	rows: any[];
}

const LoadingRow = ( props: LoadingRowProps ) => {
	const { headers, rows } = props;

	return (
		<>
			<Row columns={ headers } />

			{ rows.slice( 0, -1 ).map( ( _item, pos ) => (
				<Row columns={ headers } key={ pos } />
			) ) }
		</>
	);
};

export default LoadingRow;
