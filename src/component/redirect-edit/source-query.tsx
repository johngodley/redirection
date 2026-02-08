import { __ } from '@wordpress/i18n';
import { Select } from '@wp-plugin-components';
import TableRow from './table-row';
import { getSourceQuery } from './constants';

interface RedirectSourceQueryProps {
	query: string;
	regex: boolean;
	onChange: ( ev: React.ChangeEvent< HTMLSelectElement > ) => void;
	url: string | string[];
}

const RedirectSourceQuery = ( { query, regex, onChange, url }: RedirectSourceQueryProps ) => {
	if ( regex ) {
		return null;
	}

	const urlString = Array.isArray( url ) ? url.join( '' ) : url;
	const items =
		urlString.includes( '?' ) === false
			? getSourceQuery().filter( ( item ) => item.value !== 'exactorder' )
			: getSourceQuery();

	return (
		<TableRow title={ __( 'Query Parameters', 'redirection' ) } className="redirect-edit__sourcequery">
			<Select name="flag_query" items={ items } value={ query } onChange={ onChange } />
		</TableRow>
	);
};

export default RedirectSourceQuery;
