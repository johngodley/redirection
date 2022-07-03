/**
 * External dependencies
 */

import React from 'react';

/**
 * External dependencies
 */

import ApiResultRaw from './api-result-raw';
import DecodeError from 'wp-plugin-components/error/decode-error';
import { getErrorLinks } from 'lib/error-links';

const getApiErrorName = error => {
	if ( error.code ) {
		return error.code;
	}

	if ( error.name ) {
		return error.name;
	}

	return null;
};

const ApiResultError = ( error, methods ) => {
	const name = getApiErrorName( error );

	return (
		<div className="api-result-log_details" key={ methods }>
			<p>
				<span className="dashicons dashicons-no" />
			</p>

			<div>
				<p>
					{ methods.map( ( method, key ) => (
						<span key={ key } className="api-result-method_fail">
							{ method } { error.data && error.data.status }
						</span>
					) ) }

					{ name && <strong>{ name }: </strong> }
					{ error.message }
				</p>

				<DecodeError error={ error } links={ getErrorLinks() } locale="redirection" />
				<ApiResultRaw error={ error } />
			</div>
		</div>
	);
};

export default ApiResultError;
