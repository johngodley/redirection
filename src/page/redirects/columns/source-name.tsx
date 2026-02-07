import Highlighter from 'react-highlight-words';
import { isEnabled } from 'component/table/utils';
import { MATCH_SERVER } from 'lib/redirect-constants';
import { ExternalLink } from '@wp-plugin-components';

interface ActionData {
	server?: string;
}

interface Row {
	match_type: string;
	url: string;
	title: string;
	action_data: ActionData;
	enabled: boolean;
	regex: boolean;
}

interface Filters {
	url?: string;
	title?: string;
}

interface SourceNameProps {
	displaySelected: string[];
	row: Row;
	filters: Filters;
}

function getServerUrl( action_data: ActionData, url: string, matchType: string ): string {
	if ( matchType === MATCH_SERVER ) {
		return action_data.server + url;
	}

	return url;
}

function getAsLink( row: Row, content: JSX.Element ): JSX.Element {
	const { match_type, regex, action_data, url } = row;

	if ( regex ) {
		return content;
	}

	return <ExternalLink url={ getServerUrl( action_data, url, match_type ) }>{ content }</ExternalLink>;
}

function wrapEnabled( source: JSX.Element, enabled: boolean ): JSX.Element {
	if ( enabled ) {
		return source;
	}

	return <s>{ source }</s>;
}

function SourceName( props: SourceNameProps ) {
	const { displaySelected, row, filters } = props;
	const { match_type, url, title, action_data, enabled } = row;
	const serverUrl = (
		<Highlighter
			searchWords={ [ filters.url || '' ] }
			textToHighlight={ getServerUrl( action_data, url, match_type ) }
			autoEscape
		/>
	);
	const titled = <Highlighter searchWords={ [ filters.title || '' ] } textToHighlight={ title } autoEscape />;

	if ( isEnabled( displaySelected, 'title' ) && ! isEnabled( displaySelected, 'source' ) ) {
		return <p>{ getAsLink( row, wrapEnabled( title ? titled : serverUrl, enabled ) ) }</p>;
	}

	return (
		<>
			{ isEnabled( displaySelected, 'title' ) && title && (
				<p>{ getAsLink( row, wrapEnabled( titled, enabled ) ) }</p>
			) }
			{ isEnabled( displaySelected, 'source' ) && serverUrl && (
				<p>{ getAsLink( row, wrapEnabled( serverUrl, enabled ) ) }</p>
			) }
		</>
	);
}

export default SourceName;
