/**
 * External dependencies
 */

import React from 'react';
import PropTypes from 'prop-types';

const ExternalLink = ( { url, children } ) => {
	return <a href={ url } target="_blank" rel="noopener noreferrer">{ children }</a>;
};

ExternalLink.propTypes = {
	url: PropTypes.string.isRequired,
};

export default ExternalLink;
