import Highlighter from 'react-highlight-words';
import { ExternalLink } from '@wp-plugin-components';

interface ReferrerProps {
	url?: string;
	search?: string;
}

const Referrer = ( props: ReferrerProps ) => {
	const { url, search } = props;

	if ( url ) {
		return (
			<ExternalLink url={ url }>
				<Highlighter searchWords={ [ search || '' ] } textToHighlight={ url || '' } autoEscape />
			</ExternalLink>
		);
	}

	return null;
};

export default Referrer;
