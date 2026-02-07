import { __ } from '@wordpress/i18n';
import RedirectFlag from './source-flag';

interface SourceData {
	flag_query: string;
}

interface MatchData {
	source: SourceData;
}

interface Row {
	match_data: MatchData;
}

interface DefaultFlags {
	flag_query: string;
}

interface SourceQueryProps {
	defaultFlags: DefaultFlags;
	row: Row;
}

function SourceQuery( props: SourceQueryProps ) {
	const { defaultFlags, row } = props;
	const {
		match_data: { source },
	} = row;

	if ( defaultFlags.flag_query !== source.flag_query ) {
		let name: string;

		if ( source.flag_query === 'ignore' ) {
			name = __( 'Ignore Query', 'redirection' );
		} else if ( source.flag_query === 'pass' ) {
			name = __( 'Ignore & Pass Query', 'redirection' );
		} else {
			name = __( 'Exact Query', 'redirection' );
		}

		return <RedirectFlag name={ name } />;
	}

	return null;
}

export default SourceQuery;
