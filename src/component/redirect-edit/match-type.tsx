import { __ } from '@wordpress/i18n';
import { Select } from '@wp-plugin-components';
import TableRow from './table-row';
import { getMatches } from './constants';

interface MatchTypeProps {
	matchType: string;
	onChange: ( ev: React.ChangeEvent< HTMLSelectElement > ) => void;
}

const MatchType = ( { matchType, onChange }: MatchTypeProps ) => {
	return (
		<TableRow title={ __( 'Match', 'redirection' ) }>
			<Select name="match_type" value={ matchType } items={ getMatches() } onChange={ onChange } />
		</TableRow>
	);
};

export default MatchType;
