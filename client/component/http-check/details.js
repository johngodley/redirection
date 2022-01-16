/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';

/**
 * Internal dependencies
 */

import { ExternalLink } from 'wp-plugin-components';

function Details( { status, headers } ) {
	if ( status >= 500 ) {
		return 'Something is wrong with the server. This is not a problem with Redirection and you will need to resolve the error yourself.';
	}

	if ( status >= 400 && status < 500 ) {
		return __( 'An error page was returned. This is unlikely to be a problem with Redirection. {{support}}What does this mean?{{/support}}.', {
			components: { support: <ExternalLink url="https://redirection.me/support/problems/redirect-returning-404/" /> },
		} );
	}

	if ( status >= 300 && status < 400 ) {
		const agent =
			headers.find( ( item ) => item.name === 'x-redirect-agent' || item.name === 'x-redirect-by' ) || '';

		if ( agent ) {
			if ( agent.value.toLowerCase() === 'redirection' ) {
				return __( 'Redirected by Redirection.' );
			}

			return __( 'Redirected by %1s. {{support}}What does this mean?{{/support}}.', {
				args: [ agent.value ],
				components: { support: <ExternalLink url="https://redirection.me/support/problems/redirected-by-another-agent/" /> },
			} );
		}

		return __( 'Redirected by an unknown agent. {{support}}What does this mean?{{/support}}.', {
			components: { support: <ExternalLink url="https://redirection.me/support/problems/redirected-by-another-agent/" /> },
		} );
	}

	if ( status >= 200 && status < 300 ) {
		return __( 'Page was loaded.' );
	}

	return null;
}

function Step( { step, isLast } ) {
	const { request, status, statusMessage, headers } = step;
	const statusGroup = Math.floor( status / 100 ) * 100;

	return (
		<>
			<div className="redirection-httpstep">
				<div className={ `redirection-httpstep__status redirection-httpstep__${ statusGroup }` }>
					<ExternalLink url={ `https://redirect.li/http/status/code/${ status }/` } title={ statusMessage }>
						{ status }
					</ExternalLink>
				</div>

				<div className="redirection-httpstep__details">
					<p>
						<code>{ request.url }</code>
					</p>
					<p>
						<Details status={ status } headers={ headers } />
					</p>
				</div>
			</div>

			{ ! isLast && (
				<div>
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
						<path
							fillRule="evenodd"
							d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z"
							clipRule="evenodd"
						/>
					</svg>
				</div>
			) }
		</>
	);
}

function Conclusion( { steps, desiredCode, desiredTarget } ) {
	// Check desired code
	if ( steps.length === 2 && steps[ 0 ].status === desiredCode && steps[ 1 ].request.url === desiredTarget ) {
		return <p>{ __( 'Everything is working great.' ) }</p>;
	}

	return (
		<p>
			{ __(
				"It's not working and it's likely not the fault of Redirection. You can find more details on this {{support}}support page{{/support}}.",
				{
					components: {
						support: <ExternalLink url="https://redirection.me/support/problems/url-not-redirecting/" />,
					},
				}
			) }
		</p>
	);
}

function HttpDetails( { desiredTarget, desiredCode, http, url } ) {
	const { steps, status } = http;

	if ( status !== 'ok' ) {
		if ( http && ( http.status === 'error' || http.status === 'fail' ) ) {
			return <div>{ __( 'Unable to check that URL. It may not be valid or accessible.' ) }</div>;
		}

		return (
			<div>
				{ __(
					'The external URL checker returned an error. This may indicate a problem with the service and you can check again in the future.'
				) }
			</div>
		);
	}

	return (
		<div>
			{ steps.map( ( item, key ) => (
				<Step key={ key } step={ item } isLast={ key === steps.length - 1 } />
			) ) }

			{ desiredTarget && desiredCode && (
				<>
					<h3>{ __( 'Conclusion' ) }</h3>
					<Conclusion steps={ steps } desiredCode={ desiredCode } desiredTarget={ desiredTarget } />

					<p>
						{ __( 'If your browser is not behaving the same then you need to clear your browser cache.' ) }
					</p>
				</>
			) }

			<p>
				<ExternalLink url={ `https://redirect.li/http/?url=${ encodeURIComponent( url ) }` } className="button">
					{ __( 'View full redirect.li results.' ) }
				</ExternalLink>{' '}
			</p>
		</div>
	);
}

export default HttpDetails;
