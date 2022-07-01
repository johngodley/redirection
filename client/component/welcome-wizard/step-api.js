/**
 * External dependencies
 */

import React, { useEffect } from 'react';
import { translate as __ } from 'i18n-calypso';
import * as parseUrl from 'url';
import { useSelector } from 'react-redux';

/**
 * Internal dependencies
 */
import { ExternalLink } from 'wp-plugin-components';
import RestApiStatus from 'component/rest-api-status';
import apiFetch from 'wp-plugin-lib/api-fetch';
import getFirstApi from './first-api';

export default function stepAPI( { setStep, step } ) {
	const api = parseUrl.parse( Redirectioni10n.api.WP_API_root );
	const home = parseUrl.parse( Redirectioni10n.pluginBaseUrl );
	const warning = api.protocol !== home.protocol || api.host !== home.host;
	const { apiTest, api: apiX } = useSelector( ( state ) => state.settings );

	useEffect(() => {
		return () => {
			// Set the API to the best
			const api = getFirstApi( apiTest );

			// Set our REST API route
			if ( Redirectioni10n.api.routes[ api ] ) {
				apiFetch.replaceRootURLMiddleware( Redirectioni10n.api.routes[ api ] );
			}
		};
	}, []);

	return (
		<>
			<h2>{ __( 'REST API' ) }</h2>

			<p>
				{ __(
					'Redirection uses the {{link}}WordPress REST API{{/link}} to communicate with WordPress. This is enabled and working by default. Sometimes the REST API is blocked by:',
					{
						components: {
							link: <ExternalLink url="https://developer.wordpress.org/rest-api/" />,
						},
					}
				) }
			</p>

			<ul>
				<li>{ __( 'A security plugin (e.g Wordfence)' ) }</li>
				<li>{ __( 'A server firewall or other server configuration (e.g OVH)' ) }</li>
				<li>{ __( 'Caching software (e.g Cloudflare)' ) }</li>
				<li>{ __( 'Some other plugin that blocks the REST API' ) }</li>
			</ul>

			<p>
				{ __(
					'If you do experience a problem then please consult your plugin documentation, or try contacting your host support. This is generally {{link}}not a problem caused by Redirection{{/link}}.',
					{
						components: {
							link: <ExternalLink url="https://redirection.me/support/problems/rest-api/" />,
						},
					}
				) }
			</p>

			{ warning && (
				<div className="wpl-error">
					{ __(
						'You have different URLs configured on your WordPress Settings > General page, which is usually an indication of a misconfiguration, and it can cause problems with the REST API. Please review your settings.'
					) }
					<p>
						<code>{ api.protocol + '//' + api.host }</code>
					</p>
					<p>
						<code>{ home.protocol + '//' + home.host }</code>
					</p>
				</div>
			) }

			<RestApiStatus allowChange={ false } />

			<p>{ __( 'You will need at least one working REST API to continue.' ) }</p>

			<div className="wizard-buttons">
				<button className="button-primary button" onClick={ () => setStep( step + 1 ) }>
					{ __( 'Finish Setup' ) }
				</button>
			</div>
		</>
	);
}
