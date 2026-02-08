interface Row {
	enabled: boolean;
}

interface StatusColumnProps {
	row: Row;
}

function StatusColumn( { row }: StatusColumnProps ) {
	const { enabled } = row;

	if ( enabled ) {
		return <div className="redirect-status redirect-status__enabled">✓</div>;
	}

	return <div className="redirect-status redirect-status__disabled">&times;</div>;
}

export default StatusColumn;
