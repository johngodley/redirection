/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import Highlighter from 'react-highlight-words';

/**
 * Internal dependencies
 */

import ExternalLink from 'component/external-link';

function ColumnUrl( props ) {
	const { row, table } = props;
	const { url } = row;

	if ( url ) {
		return (
			<ExternalLink url={ url }>
				<Highlighter
					searchWords={ [ table.filterBy.url || table.filterBy[ 'url-exact' ] ] }
					textToHighlight={ url.substring( 0, 100 ) }
					autoEscape
				/>
			</ExternalLink>
		);
	}

	return null;
}

export default ColumnUrl;
