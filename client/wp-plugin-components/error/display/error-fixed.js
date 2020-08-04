/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'wp-plugin-lib/locale';

/**
 * Internal dependencies
 */

import ErrorDebug from '../debug';

function DisplayDefaultError( props ) {
	const { title, children } = props;

	return (
		<>
			<h2>{ title || __( 'Something went wrong 🙁' ) }</h2>

			{ children }

			<ErrorDebug { ...props } noParse />
		</>
	);
}

export default DisplayDefaultError;
