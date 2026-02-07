import { __, sprintf } from '@wordpress/i18n';
import { createInterpolateElement, ExternalLink } from '@wp-plugin-components';
import { getServerUrl } from 'lib/wordpress-url';

interface HttpHeader {
	name: string;
	value: string;
}

interface HttpRequest {
	url: string;
}

interface HttpStep {
	request: HttpRequest;
	status: number;
	statusMessage: string;
	headers: HttpHeader[];
}

interface Http {
	steps: HttpStep[];
	status?: string;
}

interface DesiredTarget {
	url_from?: string;
	server?: string;
}

interface DetailsProps {
	status: number;
	headers: HttpHeader[];
	statusMessage: string;
	isFirst: boolean;
	isMatch: boolean;
}

interface StepProps {
	step: HttpStep;
	isFirst: boolean;
	isLast: boolean;
	isMatch: boolean;
}

interface HttpDetailsProps {
	desiredTarget: DesiredTarget | string;
	desiredCode: number;
	http: Http;
	url: string;
}

function getAbsolute( url: string, base?: string ): string {
	if ( url.slice( 0, 4 ) === 'http' ) {
		return url;
	}

	return getServerUrl( base ? base : document.location.origin, url );
}

function getTarget( target: DesiredTarget | string ): string {
	if ( typeof target === 'object' && target.url_from !== undefined ) {
		return target.url_from;
	}

	return target as string;
}

function Details( { status, headers, statusMessage, isMatch: isMatching }: DetailsProps ) {
	if ( status === 0 ) {
		if ( statusMessage.length > 0 ) {
			return <>{ statusMessage }</>;
		}

		return <>{ __( 'An unknown error', 'redirection' ) }</>;
	}

	if ( status >= 500 ) {
		return (
			<>
				{ __(
					'Something is wrong with the server. This is not a problem with Redirection and you will need to resolve the error yourself.',
					'redirection'
				) }
			</>
		);
	}

	if ( status >= 400 && status < 500 ) {
		return (
			<>
				{ createInterpolateElement(
					__(
						'An error page was returned. This is unlikely to be a problem with Redirection. {{support}}What does this mean?{{/support}}.',
						'redirection'
					),
					{
						support: <ExternalLink url="https://redirection.me/support/problems/redirect-returning-404/" />,
					}
				) }
			</>
		);
	}

	if ( status >= 300 && status < 400 ) {
		if ( isMatching ) {
			return (
				<>
					{ __( 'Redirected by Redirection.', 'redirection' ) }{ ' ' }
					<span className="redirection-httpstep__match">
						{ __( 'Matches your redirect', 'redirection' ) } <span className="dashicons dashicons-yes" />
					</span>
				</>
			);
		}

		const agent =
			headers.find( ( item ) => item.name === 'x-redirect-agent' || item.name === 'x-redirect-by' ) || null;

		if ( agent ) {
			if ( agent.value.toLowerCase() === 'redirection' ) {
				return <>{ __( 'Redirected by Redirection.', 'redirection' ) }</>;
			}

			return (
				<>
					{ createInterpolateElement(
						sprintf(
							// translators: %1$s is the name of the agent that performed the redirect
							__( 'Redirected by %1$s. {{support}}What does this mean?{{/support}}.', 'redirection' ),
							agent.value
						),
						{
							support: (
								<ExternalLink url="https://redirection.me/support/problems/redirected-by-another-agent/" />
							),
						}
					) }
				</>
			);
		}

		return (
			<>
				{ createInterpolateElement(
					__( 'Redirected by an unknown agent. {{support}}What does this mean?{{/support}}.', 'redirection' ),
					{
						support: (
							<ExternalLink url="https://redirection.me/support/problems/redirected-by-another-agent/" />
						),
					}
				) }
			</>
		);
	}

	if ( status >= 200 && status < 300 ) {
		return <>{ __( 'Page was loaded.', 'redirection' ) }</>;
	}

	return null;
}

function Step( { step, isFirst, isLast, isMatch: isMatching }: StepProps ) {
	const { request, status, statusMessage, headers } = step;
	const statusGroup = Math.floor( status / 100 ) * 100;

	return (
		<>
			<div className="redirection-httpstep">
				<div
					className={ `redirection-httpstep__status redirection-httpstep__${
						statusGroup > 0 ? statusGroup : '500'
					}` }
				>
					{ parseInt( status.toString(), 10 ) > 0 ? (
						<ExternalLink
							url={ `https://redirect.li/http/status/code/${ status }/` }
							title={ statusMessage }
						>
							{ status }
						</ExternalLink>
					) : (
						'Fail'
					) }
				</div>

				<div className="redirection-httpstep__details">
					<p>
						<code>{ request.url }</code>
					</p>
					<p>
						<Details
							status={ parseInt( status.toString(), 10 ) }
							statusMessage={ statusMessage }
							headers={ headers }
							isFirst={ isFirst }
							isMatch={ isMatching }
						/>
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

function isMatch(
	headers: HttpHeader[],
	status: number,
	desiredTarget: DesiredTarget | string,
	desiredCode: number
): boolean {
	if ( ! Array.isArray( headers ) || ! desiredTarget || ! desiredCode ) {
		return false;
	}

	const agent = headers.find( ( item ) => item.name === 'x-redirect-agent' || item.name === 'x-redirect-by' );
	const location = headers.find( ( item ) => item.name === 'location' );

	if ( agent && location ) {
		if ( typeof desiredTarget === 'object' && desiredTarget.url_from === undefined ) {
			return (
				agent.value.toLowerCase() === 'redirection' &&
				desiredCode === status &&
				getAbsolute( location.value ) === getTarget( desiredTarget )
			);
		}

		if ( typeof desiredTarget === 'object' ) {
			return (
				agent.value.toLowerCase() === 'redirection' &&
				desiredCode === status &&
				getAbsolute( location.value, desiredTarget.server ) === getTarget( desiredTarget )
			);
		}
	}

	return false;
}

function HttpDetails( { desiredTarget, desiredCode, http, url }: HttpDetailsProps ) {
	const { steps } = http;

	if ( http && ( http.status === 'error' || http.status === 'fail' ) && steps.length === 0 ) {
		return <div>{ __( 'Unable to check that URL. It may not be valid or accessible.', 'redirection' ) }</div>;
	}

	return (
		<div>
			{ steps.map( ( item, key ) => (
				<Step
					key={ key }
					step={ item }
					isFirst={ key === 0 }
					isLast={ key === steps.length - 1 }
					isMatch={ isMatch( item.headers, item.status, desiredTarget, desiredCode ) }
				/>
			) ) }

			<p>
				{ desiredTarget && desiredCode && (
					<>
						{ createInterpolateElement(
							__(
								'If this is not expected then this {{support}}support page{{/support}} may help.',
								'redirection'
							),
							{
								support: (
									<ExternalLink url="https://redirection.me/support/problems/url-not-redirecting/" />
								),
							}
						) }{ ' ' }
					</>
				) }

				{ __(
					'If your browser is behaving differently then you should clear your browser cache.',
					'redirection'
				) }
			</p>
			<p>
				<ExternalLink url={ `https://redirect.li/http/?url=${ encodeURIComponent( url ) }` } className="button">
					{ __( 'View full redirect.li results.', 'redirection' ) }
				</ExternalLink>
			</p>
		</div>
	);
}

export default HttpDetails;
