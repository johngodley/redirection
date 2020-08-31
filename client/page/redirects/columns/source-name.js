/**
 * External dependencies
 */

import React from 'react';
import Highlighter from 'react-highlight-words';

/**
 * Internal dependencies
 */

import { isEnabled } from 'component/table/utils';
import { MATCH_SERVER } from 'state/redirect/selector';
import { ExternalLink } from 'wp-plugin-components';

/**
 * Get the full server URL
 * @param {object} action_data
 * @param {string} url
 * @param {string} matchType
 */
function getServerUrl( action_data, url, matchType ) {
	if ( matchType === MATCH_SERVER ) {
		return action_data.server + url;
	}

	return url;
}

function getAsLink( row, content ) {
	const { match_type, regex, action_data, url } = row;

	if ( regex ) {
		return content;
	}

	return <ExternalLink url={ getServerUrl( action_data, url, match_type ) }>{ content }</ExternalLink>;
}

function wrapEnabled( source, enabled ) {
	if ( enabled ) {
		return source;
	}

	return <strike>{ source }</strike>;
}

function SourceName( props ) {
	const { displaySelected, row, filters } = props;
	const { match_type, url, title, action_data, enabled } = row;
	const serverUrl = (
		<Highlighter
			searchWords={ [ filters.url ] }
			textToHighlight={ getServerUrl( action_data, url, match_type ) }
			autoEscape
		/>
	);
	const titled = <Highlighter searchWords={ [ filters.title ] } textToHighlight={ title } autoEscape />;

	if ( isEnabled( displaySelected, 'title' ) && ! isEnabled( displaySelected, 'source' ) ) {
		// Source or title
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
