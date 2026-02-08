import Highlighter from 'react-highlight-words';
import { ExternalLink } from '@wp-plugin-components';

interface FilterBy {
	url?: string;
	'url-exact'?: string;
}

interface Table {
	filterBy: FilterBy;
}

interface Row {
	url?: string;
}

interface ColumnUrlProps {
	row: Row;
	table: Table;
}

function ColumnUrl( props: ColumnUrlProps ) {
	const { row, table } = props;
	const { url } = row;

	if ( url ) {
		return (
			<ExternalLink url={ url }>
				<Highlighter
					searchWords={ [ table.filterBy.url || table.filterBy[ 'url-exact' ] || '' ] }
					textToHighlight={ url.substring( 0, 100 ) }
					autoEscape
				/>
			</ExternalLink>
		);
	}

	return null;
}

export default ColumnUrl;
