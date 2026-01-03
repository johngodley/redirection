interface ColumnDateProps {
	created: string;
	createdTime: string;
}

export default function ColumnDate( row: ColumnDateProps ) {
	const { created, createdTime } = row;

	return (
		<>
			{ created }
			<br />
			{ createdTime }
		</>
	);
}
