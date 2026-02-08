/**
 * External dependencies
 */

import React, { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';
import classnames from 'classnames';

/**
 * Internal dependencies
 */

import DisplayNonceError from './display/error-nonce';
import DisplayDefaultError from './display/error-default';
import DisplayKnownError from './display/error-known';
import DisplayFixedError from './display/error-fixed';
import DisplayApiError from './display/error-api';
import './style.scss';

function ErrorPaging( { current, change, total } ) {
	return (
		<div className="wpl-error__page">
			{ current > 0 && <span onClick={ () => change( current - 1 ) }>←</span> }

			{ `${ current + 1 }/${ total }` }

			{ current + 1 < total && <span onClick={ () => change( current + 1 ) }>→</span> }
		</div>
	);
}

/**
 * Get the type of error message to display
 *
 * @param {Error[]|ApiError[]} errors - Array of errors
 * @param {string} type - Chosen type
 */
function getErrorType( errors, type ) {
	if ( errors[ 0 ].code === 'rest_cookie_invalid_nonce' ) {
		return DisplayNonceError;
	}

	if ( errors[ 0 ]?.jsonData?.status === 400 ) {
		return DisplayApiError;
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
 * @param {string} props.locale - Locale for text
 * @param {Error[]|Error|ApiError[]} props.errors - Array of errors or single error
 * @param {string[]} [props.details] - Array of site details
 * @param {ClearCallback} [props.onClear] - Callback to clear the error
 * @param {string|React|Element|import('react').ReactChild} props.children - Child components
 * @param {string|React} [props.title] - Title
 * @param {('error'|'fixed')} [props.type] - Type of error
 */
function Error( props ) {
	const { onClear, locale, mini = false, type = '' } = props;
	const errors = Array.isArray( props.errors ) ? props.errors : [ props.errors ];
	const [ currentError, setCurrentError ] = useState( 0 );

	useEffect(() => {
		if ( ! mini && errors.length > 0 ) {
			window.scrollTo( 0, 0 );
		}
	}, [ errors ]);

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

			{ errors.length > 1 && (
				<ErrorPaging current={ currentError } change={ setCurrentError } total={ errors.length } />
			) }

			<Error error={ errors[ currentError ] } { ...props } />
		</div>
	);
}

export default Error;
