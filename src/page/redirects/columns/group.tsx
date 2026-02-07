import Badge from '@wp-plugin-components/badge';

interface GroupRow {
	id: number;
	name: string;
	moduleName: string;
}

interface Group {
	rows: GroupRow[];
}

interface Row {
	group_id: number;
}

interface GroupColumnProps {
	row: Row;
	group: Group;
}

function GroupColumnn( props: GroupColumnProps ) {
	const { row, group } = props;
	const foundGroup = group.rows.find( ( found ) => found.id === row.group_id );

	if ( foundGroup ) {
		return (
			<div className="redirect-column-wrap">
				{ foundGroup.name } <Badge>{ foundGroup.moduleName }</Badge>
			</div>
		);
	}

	return null;
}

export default GroupColumnn;
