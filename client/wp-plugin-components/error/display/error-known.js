/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'wp-plugin-wp-plugin-lib/locale';

/**
 * Internal dependencies
 */

import ErrorDebug from '../debug';
import DecodeError from '../decode-error';

/**
 *
 * @param {object} props
 * @param {string} props.title
 * @param {boolean} props.mini
 */
function DisplayKnownError( props ) {
	const { title, children, errors } = props;

	return (
		<>
			<h2>{ title || __( 'Something went wrong 🙁' ) }</h2>

			<div className="wpl-error__detail">
				{ errors.map( ( error, pos ) => (
					<DecodeError error={ error } key={ pos } />
				) ) }
			</div>

			{ children }

			<ErrorDebug { ...props } />
		</>
	);
}

export default DisplayKnownError;
