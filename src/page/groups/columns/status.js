function StatusColumn( { row } ) {
	const { enabled } = row;

	if ( enabled ) {
		return <div className="redirect-status redirect-status__enabled">✓</div>;
	}

	return <div className="redirect-status redirect-status__disabled">❌</div>;
}

export default StatusColumn;
