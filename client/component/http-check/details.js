/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';

/**
 * Internal dependencies
 */

import ExternalLink from 'wp-plugin-components/external-link';
import { isRedirection } from 'lib/wordpress-url';

const HttpDetails = props => {
	const { action_code, action_data } = props.item;
	const { status, headers = [] } = props.http;
	const location = headers.find( item => item.name === 'location' );
	const redirection = isRedirection( headers );
	const target = action_data.url_from ? action_data.url_from : action_data.url;
	const matches = action_code === status && location && location.value === target && redirection;

	return (
		<div className="redirection-httpcheck_results">
			<div className="redirection-httpcheck_status">
				{ matches && <span className="dashicons dashicons-yes"></span> }
				{ ! matches && <span className="dashicons dashicons-no"></span> }
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
						{
							location
								? __( '{{code}}%(status)d{{/code}} to {{code}}%(url)s{{/code}}', {
									args: {
										status,
										url: location.value,
									},
									components: {
										code: <code />,
									},
								} )
								: status
						}
					</span>
				</p>
				<p>
					<strong>{ __( 'Agent' ) }: </strong>

					<span>{ redirection ? __( 'Using Redirection' ) : __( 'Not using Redirection' ) }</span>
				</p>
				{ location && ! redirection && <p><ExternalLink url="https://redirection.me/support/problems/url-not-redirecting/">{ __( 'What does this mean?' ) }</ExternalLink></p> }
			</div>
		</div>
	);
};

export default HttpDetails;
