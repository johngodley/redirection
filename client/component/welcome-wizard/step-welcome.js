/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';

/**
 * Internal dependencies
 */
import { ExternalLink } from 'wp-plugin-components';
import { STEP_DATABASE, STEP_BASIC } from './constants';

export default function StepWelcome( { setStep, setOptions } ) {
	function startAutomatic() {
		setOptions( { manual: false } );
		setStep( STEP_BASIC );
	}

	function startManual() {
		setOptions( { manual: true } );
		setStep( STEP_DATABASE );
	}

	return (
		<>
			<h2>{ __( 'Welcome to Redirection 🚀🎉' ) }</h2>

			<p>
				{ __(
					'Thank you for installing and using Redirection v%(version)s. This plugin will allow you to manage 301 redirections, keep track of 404 errors, and improve your site, with no knowledge of Apache or Nginx needed.',
					{
						args: {
							version: Redirectioni10n.version,
						},
					}
				) }
			</p>
			<p>
				{ __(
					'Redirection is designed to be used on sites with a few redirects to sites with thousands of redirects.'
				) }
			</p>

			<h3>{ __( 'How do I use this plugin?' ) }</h3>
			<p>
				{ __(
					"A simple redirect involves setting a {{strong}}source URL{{/strong}} (the old URL) and a {{strong}}target URL{{/strong}} (the new URL). Here's an example:",
					{
						components: {
							strong: <strong />,
						},
					}
				) }
			</p>

			<table className="redirect-edit">
				<tbody>
					<tr>
						<th>{ __( 'Source URL' ) }:</th>
						<td>
							<input
								type="text"
								className="regular-text"
								readOnly
								value={ __( '(Example) The source URL is your old or original URL' ) }
							/>
						</td>
					</tr>
					<tr>
						<th>{ __( 'Target URL' ) }:</th>
						<td>
							<input
								type="text"
								className="regular-text"
								readOnly
								value={ __( '(Example) The target URL is the new URL' ) }
							/>
						</td>
					</tr>
				</tbody>
			</table>

			<p>
				{ __( "That's all there is to it - you are now redirecting! Note that the above is just an example." ) }
			</p>
			<p>
				{ __( 'Full documentation can be found on the {{link}}Redirection website.{{/link}}', {
					components: {
						link: <ExternalLink url="https://redirection.me/support/" />,
					},
				} ) }
			</p>

			<h3>{ __( 'Some features you may find useful are' ) }:</h3>

			<ul>
				<li>
					{ __(
						'{{link}}Monitor 404 errors{{/link}}, get detailed information about the visitor, and fix any problems',
						{
							components: {
								link: <ExternalLink url="https://redirection.me/support/tracking-404-errors/" />,
							},
						}
					) }
				</li>
				<li>
					{ __( '{{link}}Import{{/link}} from .htaccess, CSV, and a variety of other plugins', {
						components: {
							link: <ExternalLink url="https://redirection.me/support/import-export-redirects/" />,
						},
					} ) }
				</li>
				<li>
					{ __(
						'More powerful URL matching, including {{regular}}regular expressions{{/regular}}, and {{other}}other conditions{{/other}}',
						{
							components: {
								regular: (
									<ExternalLink url="https://redirection.me/support/redirect-regular-expressions/" />
								),
								other: <ExternalLink url="https://redirection.me/support/matching-redirects/" />,
							},
						}
					) }
				</li>
				<li>{ __( 'Check a URL is being redirected' ) }</li>
			</ul>

			<h3>{ __( "What's next?" ) }</h3>
			<p>{ __( 'First you will be asked a few questions, and then Redirection will set up your database.' ) }</p>

			<div className="wizard-buttons">
				<button className="button-primary button" onClick={ startAutomatic }>
					{ __( 'Start Setup' ) }
				</button>{' '}
				<button className="button-secondary button" onClick={ startManual }>
					{ __( 'Manual Setup' ) }
				</button>
			</div>
		</>
	);
}
