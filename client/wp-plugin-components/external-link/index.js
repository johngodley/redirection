/**
 * External dependencies
 */

import React from 'react';

/**
 * Wrap a component in an external link, with appropriate `rel` properties.
 *
 * @param {object} props - Component props
 * @param {string} props.url - URL
 * @param {string|React} props.children - Child components
 */
const ExternalLink = ( { url, children } ) => {
	return <a href={ url } target="_blank" rel="noopener noreferrer">{ children }</a>;
};

export default ExternalLink;
