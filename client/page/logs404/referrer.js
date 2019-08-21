/**
 * External dependencies
 */

import React from 'react';
import * as parseUrl from 'url';
import Highlighter from 'react-highlight-words';

/**
 * Internal dependencies
 */
import ExternalLink from 'component/external-link';

const Referrer = props => {
	const { url, search } = props;

	if ( url ) {
		const domain = parseUrl.parse( url ).hostname;

		return (
			<ExternalLink url={ url }>
				<Highlighter searchWords={ [ search ] } textToHighlight={ domain || '' } />
			</ExternalLink>
		);
	}

	return null;
};

export default Referrer;
