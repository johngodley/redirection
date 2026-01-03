import { getSourceFlags } from 'component/redirect-edit/constants';
import RedirectFlag from './source-flag';

interface SourceData {
	[ key: string ]: any;
	flag_query?: any;
}

interface MatchData {
	source: SourceData;
}

interface Row {
	match_data: MatchData;
}

interface SourceFlagsProps {
	row: Row;
	defaultFlags: { [ key: string ]: any };
}

function SourceFlags( props: SourceFlagsProps ) {
	const { row, defaultFlags } = props;
	const {
		match_data: { source },
	} = row;

	return (
		<>
			{ Object.keys( source )
				.filter( ( key ) => defaultFlags[ key ] !== source[ key ] && key !== 'flag_query' )
				.map( ( key ) => {
					const displayName = getSourceFlags().find( ( item ) => item.value === key );

					return (
						<RedirectFlag
							key={ key }
							name={ displayName?.label || '' }
							className={ 'redirect-source__' + key }
						/>
					);
				} ) }
		</>
	);
}

export default SourceFlags;
