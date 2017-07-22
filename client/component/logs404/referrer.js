/**
 * External dependencies
 */

import React from 'react';
import * as parseUrl from 'url';

const Referrer = props => {
	const { url } = props;

	if ( url ) {
		const domain = parseUrl.parse( url ).hostname;

		return (
			<a href={ url } rel="noreferrer noopener" target="_blank">{ domain }</a>
		);
	}

	return null;
};

export default Referrer;
