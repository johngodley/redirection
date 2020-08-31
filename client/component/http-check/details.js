/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';

/**
 * Internal dependencies
 */

import { ExternalLink } from 'wp-plugin-components';
import { isRedirection, isCached } from 'lib/wordpress-url';

function HttpStatus( props ) {
	const { status, headers, code, target } = props;
	const location = headers.find( ( item ) => item.name === 'location' );
	const redirection = isRedirection( headers );

	if ( code === status && location && location.value === target && redirection ) {
		return <span className="dashicons dashicons-yes" />;
	}

	if ( status === 500 || isCached( headers ) ) {
		return <span className="dashicons dashicons-warning" />;
	}

	return <span className="dashicons dashicons-no" />;
}

function HttpDetails( props ) {
	const { action_code, action_data } = props.item;
	const { status, headers = [] } = props.http;
	const location = headers.find( item => item.name === 'location' );
	const redirection = isRedirection( headers );
	const target = action_data.url_from ? action_data.url_from : action_data.url;

	return (
		<div className="redirection-httpcheck_results">
			<div className="redirection-httpcheck_status">
				<HttpStatus status={ status } headers={ headers } code={ action_code } target={ target } />
			</div>
			<div className="redirection-httpcheck_info">
				<p>
					<strong>{ __( 'Expected' ) }: </strong>

					<span>
						{ __( '{{code}}%(status)d{{/code}} to {{code}}%(target)s{{/code}}', {
							args: {
								status: action_code,
								target,
							},
							components: {
								code: <code />,
							},
						} ) }
					</span>
				</p>
				<p>
					<strong>{ __( 'Found' ) }: </strong>

					<span>
						{ location
							? __( '{{code}}%(status)d{{/code}} to {{code}}%(url)s{{/code}}', {
									args: {
										status,
										url: location.value,
									},
									components: {
										code: <code />,
									},
							  } )
							: status }
					</span>
				</p>
				<p>
					<strong>{ __( 'Agent' ) }: </strong>

					<span>{ redirection ? __( 'Using Redirection' ) : __( 'Not using Redirection' ) }</span>
				</p>

				{ location && ! redirection && (
					<p>
						<ExternalLink url="https://redirection.me/support/problems/url-not-redirecting/">
							{ __( 'Something else other than Redirection is redirecting this URL.' ) }
						</ExternalLink>
					</p>
				) }

				{ isCached( headers ) && (
					<p>
						<ExternalLink url="https://redirection.me/support/problems/url-not-redirecting/">
							{ __( 'Your URL is cached and the cache may need to be cleared.' ) }
						</ExternalLink>
					</p>
				) }

				{ ( status >= 500 ) && (
					<p>
						<ExternalLink url="https://redirection.me/support/problems/url-not-redirecting/">
							{ __( 'Cannot connect to the server to determine the redirect status.' ) }
						</ExternalLink>
					</p>
				) }
			</div>
		</div>
	);
};

export default HttpDetails;
