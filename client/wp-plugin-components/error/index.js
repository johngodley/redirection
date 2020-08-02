/**
 * External dependencies
 */

import React, { useEffect } from 'react';
import { translate as __ } from 'wp-plugin-lib/locale';
import classnames from 'classnames';

/**
 * Internal dependencies
 */

import DisplayNonceError from './display/error-nonce';
import DisplayDefaultError from './display/error-default';
import DisplayKnownError from './display/error-known';
import DisplayFixedError from './display/error-fixed';
import './style.scss';

function removeSameError( errors ) {
	return errors.filter( ( item, index ) => {
		for ( let pos = index + 1; index < errors.length - 1; index++ ) {
			if ( item.code && errors[ pos ].code && item.code === errors[ pos ].code ) {
				return false;
			}

			if ( item.message && errors[ pos ].message && item.message === errors[ pos ].message ) {
				return false;
			}
		}

		return true;
	} );
}

/**
 * Get the type of error message to display
 *
 * @param {Error[]|ApiError[]} errors - Array of errors
 * @param {string} type - Chosen type
 */
function getErrorType( errors, type ) {
	if ( errors.length > 0 && errors[ 0 ].code === 'rest_cookie_invalid_nonce' ) {
		return DisplayNonceError;
	}

	if ( type === 'error' ) {
		return DisplayKnownError;
	}

	if ( type === 'fixed' ) {
		return DisplayFixedError;
	}

	return DisplayDefaultError;
}

/**
 * @callback ClearCallback
 */

/**
 * Show errors
 *
 * @param {object} props - Component props
 * @param {boolean} [props.mini] - Show a small version for inline usage
 * @param {Error[]|ApiError[]} props.errors - Array of errors
 * @param {ClearCallback} [props.onClear] - Callback to clear the error
 * @param {string|React|Element} props.children - Child components
 * @param {string|React} [props.title] - Title
 * @param {('error'|'fixed')} [props.type] - Type of error
 */
function Error( props ) {
	const { onClear, mini = false, type = '' } = props;
	const errors = props.errors ? removeSameError( props.errors ) : [];

	useEffect( () => {
		if ( ! mini && errors.length > 0 ) {
			window.scrollTo( 0, 0 );
		}
	}, [ errors ] );

	if ( errors.length === 0 ) {
		return null;
	}

	const Error = getErrorType( errors, type );

	return (
		<div className={ classnames( 'wpl-error', { 'wpl-error__mini': mini } ) }>
			{ onClear && (
				<div className="closer" onClick={ onClear }>
					<span className="dashicons dashicons-no-alt" />
				</div>
			) }

			<Error errors={ errors } { ...props } />
		</div>
	);
}

export default Error;
