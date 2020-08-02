/**
 * External dependencies
 */

import React from 'react';

/**
 * Extract as much information from an error
 *
 * @param {Error|ApiError} error - Error
 * @returns {object}
 */
export default function getErrorDetails( error ) {
	if ( error.code === 0 ) {
		return error.message;
	}

	if ( error.data && error.data.wpdb ) {
		return (
			<span>
				{ `${ error.message } (${ error.code })` }: <code>{ error.data.wpdb }</code>
			</span>
		);
	}

	if ( error.code ) {
		return (
			<>
				{ error.message } (<code>{ error.code }</code>)
			</>
		);
	}

	return error.message;
}
