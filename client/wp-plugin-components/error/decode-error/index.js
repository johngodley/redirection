/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'wp-plugin-wp-plugin-lib/locale';

/**
 * Internal dependencies
 */

import { ExternalLink } from 'wp-plugin-components';
import apiFetch from 'wp-plugin-lib/api-fetch';
import {
	isServerError,
	isEmptyResponse,
	isSecurityPlugin,
	is404,
	isTooBig,
	isRESTDisabled,
	isUnknownError,
	isParseError,
	isFailedFetch,
	isRedirectedAPI,
} from './error-detect';
import getErrorDetails from './error-details';
import extractPhpError from './php-details';

export function shouldShowInformation( error ) {
	if ( is404( error ) ) {
		return false;
	}

	if ( isRedirectedAPI( error ) ) {
		return false;
	}

	if ( isRESTDisabled( error ) ) {
		return false;
	}

	return true;
}

export function shouldHideDebug( error ) {
	if ( is404( error ) ) {
		return true;
	}

	if ( isRedirectedAPI( error ) ) {
		return true;
	}

	if ( isRESTDisabled( error ) ) {
		return true;
	}

	return false;
}

/**
 * Decode an error message
 *
 * @param {object} props - Component props
 * @param {Error|ApiError} props.error - Error
 */
const DecodeError = ( { error } ) => {
	if ( typeof error === 'string' ) {
		return <p>{ error }</p>;
	}

	if ( isEmptyResponse( error ) ) {
		return (
			<p>
				{ __(
					'WordPress did not return a response. This could mean an error occurred or that the request was blocked. Please check your server error_log.'
				) }
			</p>
		);
	}

	if ( isSecurityPlugin( error ) ) {
		return (
			<>
				<p>{ getErrorDetails( error ) }</p>
				<p>
					{ __(
						'Your REST API is probably being blocked by a security plugin. Please disable this, or configure it to allow REST API requests.'
					) }
				</p>
				<p>
					<ExternalLink url="https://searchregex.com/support/problems/rest-api/">
						{ __( 'Read this REST API guide for more information.' ) }
					</ExternalLink>
				</p>
			</>
		);
	}

	if ( is404( error ) ) {
		return (
			<>
				<p>{ getErrorDetails( error ) }</p>
				<p>
					{ __(
						'Your REST API is returning a 404 page. This is almost certainly a plugin or server configuration issue and not a problem with Redirection.'
					) }
				</p>
				<ul>
					<li>
						{ __(
							'Can you access your {{api}}REST API{{/api}} without it redirecting? You will need to fix any errors.',
							{
								components: {
									api: <ExternalLink url={ apiFetch.rootURLMiddleware.rootURL } />,
								},
							}
						) }
					</li>
					<li>
						{ __( 'Check your {{link}}Site Health{{/link}} and fix any issues.', {
							components: {
								link: <ExternalLink url="site-health.php" />,
							},
						} ) }
					</li>
					<li>
						{ __(
							'Your server configuration is blocking access to the REST API. You will need to fix this.'
						) }
					</li>
					<li>
						{ __(
							'A security plugin or firewall is blocking access. You will need to whitelist the REST API.'
						) }
					</li>
				</ul>
				<p>
					<ExternalLink url="https://searchregex.com/support/problems/rest-api/">
						{ __( 'Read this REST API guide for more information.' ) }
					</ExternalLink>
				</p>
			</>
		);
	}

	if ( isRedirectedAPI( error ) ) {
		return <p>{ __( 'Your REST API is being redirected. Please remove the redirection for the API.' ) }</p>;
	}

	if ( isTooBig( error ) ) {
		return (
			<p>
				{ __(
					'Your server has rejected the request for being too big. You will need to reconfigure it to continue.'
				) }
			</p>
		);
	}

	if ( isUnknownError( error ) ) {
		return <p>{ __( 'An unknown error occurred.' ) }</p>;
	}

	if ( isServerError( error ) ) {
		return (
			<>
				<p>{ getErrorDetails( error ) }</p>
				<p>
					{ __(
						'This could be a security plugin, or your server is out of memory or has an external error. Please check your server error log'
					) }
				</p>
				<p>
					<ExternalLink url="https://searchregex.com/support/problems/rest-api/#http">
						{ __( 'Read this REST API guide for more information.' ) }
					</ExternalLink>
				</p>
			</>
		);
	}

	if ( isRESTDisabled( error ) ) {
		return <p>{ __( 'Your WordPress REST API has been disabled. You will need to enable it to continue.' ) }</p>;
	}

	if ( isParseError( error ) ) {
		const php = extractPhpError( '' ?? error?.request?.apiFetch?.body );

		return (
			<>
				<p>{ getErrorDetails( error ) }</p>
				<p>
					{ __(
						'WordPress returned an unexpected message. This could be a PHP error from another plugin, or data inserted by your theme.'
					) }
				</p>
				{ php.length > 1 && (
					<p>
						<strong>{ __( 'Possible cause' ) }:</strong> <code>{ php.substr( 0, 1000 ) }</code>
					</p>
				) }
			</>
		);
	}

	if ( isFailedFetch( error ) ) {
		return (
			<>
				<p>{ getErrorDetails( error ) }</p>
				<p>
					{ __(
						'Unable to make request due to browser security. This is typically because your WordPress and Site URL settings are inconsistent, or the request was blocked by your site CORS policy.'
					) }
				</p>
				<p>
					<ExternalLink url="https://searchregex.com/support/problems/rest-api/#url">
						{ __( 'Read this REST API guide for more information.' ) }
					</ExternalLink>
				</p>
			</>
		);
	}

	return <p>{ getErrorDetails( error ) }</p>;
};

export default DecodeError;
