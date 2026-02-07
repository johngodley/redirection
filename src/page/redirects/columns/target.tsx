import Highlighter from 'react-highlight-words';
import { MATCH_URL } from 'lib/redirect-constants';

interface ActionData {
	url?: string;
}

interface Row {
	match_type: string;
	action_data: ActionData;
}

interface Filters {
	target?: string;
}

interface TargetProps {
	row: Row;
	filters: Filters;
}

function Target( props: TargetProps ) {
	const { row, filters } = props;
	const { match_type, action_data } = row;

	if ( match_type === MATCH_URL && action_data ) {
		return (
			<span className="target">
				<Highlighter
					searchWords={ [ filters.target ?? '' ] }
					textToHighlight={ action_data.url ?? '' }
					autoEscape
				/>
			</span>
		);
	}

	return null;
}

export default Target;
