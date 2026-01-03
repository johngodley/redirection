interface BulkActionProps {
	children: React.ReactNode;
}

const BulkAction = ( { children }: BulkActionProps ) => <div className="alignleft actions">{ children }</div>;

export default BulkAction;
