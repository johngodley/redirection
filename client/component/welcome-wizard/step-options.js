/**
 * External dependencies
 */

import { __ } from '@wordpress/i18n';
import { useSelector } from 'react-redux';

/**
 * Internal dependencies
 */

import { ExternalLink, createInterpolateElement } from 'wp-plugin-components';

export default function StepOptions( { setStep, step, options, setOptions } ) {
	const { log = false, ip = false, monitor = false } = options?.settings ?? {};
	const { importers } = useSelector( ( state ) => state.io );
	const nextStep = importers.length === 0 ? step + 2 : step + 1;

	function setValue( ev ) {
		setOptions( { settings: { ...options.settings, [ ev.target.name ]: ev.target.checked } } );
	}

	function setLogChecked( ev ) {
		setOptions( {
			settings: {
				...options.settings,
				[ ev.target.name ]: ev.target.checked,
				ip: ev.target.checked ? ip : false,
			},
		} );
	}

	return (
		<>
			<h2>{ __( 'Basic Setup', 'redirection' ) }</h2>

			<p>{ __( 'These are some options you may want to enable now. They can be changed at any time.', 'redirection' ) }</p>

			<div className="wizard-option">
				<p>
					<label>
						<input
							name="monitor"
							type="checkbox"
							checked={ monitor }
							onChange={ setValue }
						/>
						{ __( 'Monitor permalink changes in WordPress posts and pages', 'redirection' ) }.
					</label>
				</p>
				<p>
					{ __(
						'If you change the permalink in a post or page then Redirection can automatically create a redirect for you.', 'redirection'
					) }
					&nbsp;
					{ createInterpolateElement(
						__( '{{link}}Read more about this.{{/link}}', 'redirection' ),
						{
							link: <ExternalLink url="https://redirection.me/support/options/#monitor" />,
						},
					) }
				</p>
			</div>

			<div className="wizard-option">
				<p>
					<label>
						<input name="log" type="checkbox" checked={ log } onChange={ setLogChecked } />
						{ __( 'Keep a log of all redirects and 404 errors.', 'redirection' ) }
					</label>
				</p>
				<p>
					{ __(
						'Storing logs for redirects and 404s will allow you to see what is happening on your site. This will increase your database storage requirements.', 'redirection'
					) }
					&nbsp;
					{ createInterpolateElement(
						__( '{{link}}Read more about this.{{/link}}', 'redirection' ),
						{
							link: <ExternalLink url="https://redirection.me/support/logs/" />,
						},
					) }
				</p>
			</div>

			<div className={ log ? 'wizard-option' : 'wizard-option wizard-option_disabled' }>
				<p>
					<label>
						<input name="ip" type="checkbox" disabled={ !log } checked={ ip } onChange={ setValue } />
						{ __( 'Store IP information for redirects and 404 errors.', 'redirection' ) }
					</label>
				</p>
				<p>
					{ __(
						'Storing the IP address allows you to perform additional log actions. Note that you will need to adhere to local laws regarding the collection of data (for example GDPR).', 'redirection'
					) }
					&nbsp;
					{ createInterpolateElement(
						__( '{{link}}Read more about this.{{/link}}', 'redirection' ),
						{
							link: <ExternalLink url="https://redirection.me/support/privacy-gdpr/" />,
						},
					) }
				</p>
			</div>

			<div className="wizard-buttons">
				<button className="button-primary button" onClick={ () => setStep( nextStep ) }>
					{ __( 'Continue', 'redirection' ) }
				</button>
				&nbsp;
				<button className="button" onClick={ () => setStep( step - 1 ) }>
					{ __( 'Go back', 'redirection' ) }
				</button>
			</div>
		</>
	);
}
