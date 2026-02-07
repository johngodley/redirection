import { __ } from '@wordpress/i18n';
import Highlighter from 'react-highlight-words';
import { RowActions, RowAction } from 'component/table/row-action';

interface FilterBy {
	ip?: string;
}

interface Table {
	filterBy: FilterBy;
	groupBy: string;
}

interface Row {
	ip: string;
}

interface ColumnIpProps {
	row: Row;
	table: Table;
	onFilter: ( filter: { ip: string } ) => void;
	isSaving?: boolean;
}

export default function ColumnIp( props: ColumnIpProps ) {
	const { row, table, onFilter, isSaving } = props;
	const { ip } = row;

	if ( ! ip ) {
		return null;
	}

	return (
		<>
			<a href={ 'https://redirect.li/ip/?ip=' + encodeURIComponent( ip ) }>
				<Highlighter searchWords={ [ table.filterBy.ip || '' ] } textToHighlight={ ip } autoEscape />
			</a>

			{ table.groupBy === '' && (
				<RowActions
					{ ...( isSaving !== undefined ? { disabled: isSaving } : {} ) }
					actions={ [
						<RowAction key="filter-ip" onClick={ () => onFilter( { ip } ) }>
							{ __( 'Filter by IP', 'redirection' ) }
						</RowAction>,
					] }
				/>
			) }
		</>
	);
}
