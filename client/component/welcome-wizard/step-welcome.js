/**
 * External dependencies
 */

import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { ExternalLink, createInterpolateElement } from 'wp-plugin-components';
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
			<h2>{ __( 'Welcome to Redirection 🚀🎉', 'redirection' ) }</h2>

			<p>
				{ createInterpolateElement(
					sprintf(
						__( 'Thank you for installing and using Redirection v%(version)s. This plugin will allow you to manage 301 redirections, keep track of 404 errors, and improve your site, with no knowledge of Apache or Nginx needed.', 'redirection' ),
					),
					{
						version: window.Redirectioni10n.version,
					},
				) }
			</p>
			<p>
				{ __(
					'Redirection is designed to be used on sites with a few redirects to sites with thousands of redirects.', 'redirection'
				) }
			</p>

			<h3>{ __( 'How do I use this plugin?', 'redirection' ) }</h3>
			<p>
				{ createInterpolateElement(
					__(
						"A simple redirect involves setting a {{strong}}source URL{{/strong}} (the old URL) and a {{strong}}target URL{{/strong}} (the new URL). Here's an example:",
						'redirection'
					),
					{
						strong: <strong />,
					}
				) }
			</p>

			<table className="redirect-edit">
				<tbody>
					<tr>
						<th>{ __( 'Source URL', 'redirection' ) }:</th>
						<td>
							<input
								type="text"
								className="regular-text"
								readOnly
								value={ __( '(Example) The source URL is your old or original URL', 'redirection' ) }
							/>
						</td>
					</tr>
					<tr>
						<th>{ __( 'Target URL', 'redirection' ) }:</th>
						<td>
							<input
								type="text"
								className="regular-text"
								readOnly
								value={ __( '(Example) The target URL is the new URL', 'redirection' ) }
							/>
						</td>
					</tr>
				</tbody>
			</table>

			<p>
				{ __( "That's all there is to it - you are now redirecting! Note that the above is just an example.", 'redirection' ) }
			</p>
			<p>
				{ createInterpolateElement(
					__( 'Full documentation can be found on the {{link}}Redirection website.{{/link}}', 'redirection' ),
					{
						link: <ExternalLink url="https://redirection.me/support/" />,
					},
				) }
			</p>

			<h3>{ __( 'Some features you may find useful are', 'redirection' ) }:</h3>

			<ul>
				<li>
					{ createInterpolateElement(
						__(
							'{{link}}Monitor 404 errors{{/link}}, get detailed information about the visitor, and fix any problems',
							'redirection'
						),
						{
							link: <ExternalLink url="https://redirection.me/support/tracking-404-errors/" />,
						}
					) }
				</li>
				<li>
					{ createInterpolateElement(
						__( '{{link}}Import{{/link}} from .htaccess, CSV, and a variety of other plugins', 'redirection' ),
						{
							link: <ExternalLink url="https://redirection.me/support/import-export-redirects/" />,
						},
					) }
				</li>
				<li>
					{ createInterpolateElement(
						__(
							'More powerful URL matching, including {{regular}}regular expressions{{/regular}}, and {{other}}other conditions{{/other}}',
							'redirection'
						),
						{
							regular: (
								<ExternalLink url="https://redirection.me/support/redirect-regular-expressions/" />
							),
							other: <ExternalLink url="https://redirection.me/support/matching-redirects/" />,
						}
					) }
				</li>
				<li>{ __( 'Check a URL is being redirected', 'redirection' ) }</li>
			</ul>

			<h3>{ __( "What's next?" ) }</h3>
			<p>{ __( 'First you will be asked a few questions, and then Redirection will set up your database.', 'redirection' ) }</p>

			<div className="wizard-buttons">
				<button className="button-primary button" onClick={ startAutomatic }>
					{ __( 'Start Setup', 'redirection' ) }
				</button>{ ' ' }
				<button className="button-secondary button" onClick={ startManual }>
					{ __( 'Manual Setup', 'redirection' ) }
				</button>
			</div>
		</>
	);
}
