/**
 * External dependencies
 */

import React from 'react';
import * as parseUrl from 'url';
import Highlighter from 'react-highlight-words';

/**
 * Internal dependencies
 */
import { ExternalLink } from 'wp-plugin-components';

const Referrer = ( props ) => {
	const { url, search } = props;

	if ( url ) {
		return (
			<ExternalLink url={ url }>
				<Highlighter searchWords={ [ search ] } textToHighlight={ url || '' } autoEscape />
			</ExternalLink>
		);
	}

	return null;
};

export default Referrer;
