interface TableButtonsProps {
	enabled?: boolean;
	children: React.ReactNode;
}

const TableButtons = ( props: TableButtonsProps ) => {
	const { enabled = true, children } = props;

	if ( enabled ) {
		return <div className="table-buttons">{ children }</div>;
	}

	return null;
};

export default TableButtons;
