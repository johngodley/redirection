/**
 * External dependencies
 */

import React from 'react';

/**
 * Wrap a component in an external link, with appropriate `rel` properties.
 *
 * @param {object} props - Component props
 * @param {string} props.url - URL
 * @param {string|undefined} [props.className] - Classname
 * @param {string|undefined} [props.title] - Title
 * @param {string|React|object|import('react').ReactChild} [props.children] - Child components
 */
const ExternalLink = ( { url, children, title = undefined, className = undefined } ) => {
	return <a href={ url } target="_blank" rel="noopener noreferrer" title={ title } className={ className }>{ children }</a>;
};

export default ExternalLink;
