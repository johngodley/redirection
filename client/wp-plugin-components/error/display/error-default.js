/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'wp-plugin-wp-plugin-lib/locale';

/**
 * Internal dependencies
 */

import ErrorDebug from '../debug';
import DecodeError, { shouldHideDebug, shouldShowInformation } from '../decode-error';

function DisplayDefaultError( props ) {
	const { title, children, errors } = props;
	const showInfo = errors.map( shouldShowInformation ).filter( item => item ).length > 0;
	const hideDebug = errors.map( shouldHideDebug ).filter( item => item ).length > 0;

	return (
		<>
			<h2>{ title || __( 'Something went wrong 🙁' ) }</h2>

			<div className="wpl-error__title">
				{ errors.map( ( error, pos ) => (
					<DecodeError error={ error } key={ pos } />
				) ) }
			</div>

			{ showInfo && children }

			<ErrorDebug { ...props } { ...( hideDebug ? { mini: true } : {} ) } />
		</>
	);
}

export default DisplayDefaultError;
