/**
 * External dependencies
 */

import React from 'react';
import * as parseUrl from 'url';

import ExternalLink from 'component/external-link';

const Referrer = props => {
	const { url } = props;

	if ( url ) {
		const domain = parseUrl.parse( url ).hostname;

		return (
			<ExternalLink url={ url }>{ domain }</ExternalLink>
		);
	}

	return null;
};

export default Referrer;
