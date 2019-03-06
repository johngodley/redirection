/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'lib/locale';
import * as parseUrl from 'url';

/**
 * Internal dependencies
 */
import ExternalLink from 'component/external-link';
import Database from 'component/database';
import { restApi } from 'page/options/options-form';
import Error from 'component/error';
import Spinner from 'component/spinner';
import { checkApi, saveSettings } from 'state/settings/action';
import { STATUS_FAILED } from 'state/settings/type';
import './style.scss';

const ApiResultIcon = ( { result, method } ) => {
	if ( result === undefined || result[ method ] === undefined ) {
		return <Spinner />;
	}

	if ( result[ method ] === true ) {
		return <span className="dashicons dashicons-yes"></span>;
	}

	return <span className="dashicons dashicons-no"></span>;
};

const ApiResult = ( { item, result, method, alt } ) => {
	return (
		<div className={ 'api-result' + ( alt ? ' api-result-alt' : '' ) }>
			<ApiResultIcon result={ result } method={ method } /> <code>{ method }</code> { item.text }

			{ result[ method ] && result[ method ] !== true &&
				<React.Fragment>
					&nbsp; - <ExternalLink url="https://redirection.me/support/problems/rest-api/#http"><span className="api-result_error">HTTP { result[ method ] }</span></ExternalLink>
				</React.Fragment>
			}
		</div>
	);
};

class WelcomeWizard extends React.Component {
	constructor( props ) {
		super( props );

		this.state = {
			step: 0,
			monitor: false,
			log: false,
			ip: false,
		};
	}

	nextStep = ev => {
		const { apiTest } = this.props;

		ev.preventDefault();

		if ( this.state.step === 1 ) {
			const untested = Object.keys( Redirectioni10n.database.api ).map( id => apiTest[ id ] === undefined ? { id, url: Redirectioni10n.database.api[ id ] } : undefined );

			this.props.onCheckApi( untested.filter( item => item ) );
		}

		this.setState( { step: this.state.step + 1 } );
	}

	apiInProgress() {
		const { apiTest } = this.props;
		const progress = Object.keys( Redirectioni10n.database.api ).filter( id => apiTest[ id ] === undefined || apiTest[ id ].GET === undefined || apiTest[ id ].POST === undefined );

		return progress.length > 0;
	}

	onRetry = ev => {
		const all = Object.keys( Redirectioni10n.database.api ).map( id => ( { id, url: Redirectioni10n.database.api[ id ] } ) );

		ev.preventDefault();
		this.props.onCheckApi( all );
	}

	prevStep = ev => {
		ev.preventDefault();
		this.setState( { step: this.state.step - 1 } );
	}

	// Returns the best API route (the one with a valid GET and POST), in order they are defined. If nothing is valid, return the default
	getFirstApi() {
		const { apiTest } = this.props;
		const keys = Object.keys( apiTest );

		for ( let index = 0; index < keys.length; index++ ) {
			if ( apiTest[ index ] && apiTest[ index ].GET === true && apiTest[ index ].POST === true ) {
				return index;
			}
		}

		return 0;
	}

	finishSetup = ev => {
		// Set the API to the best
		const api = this.getFirstApi();

		if ( Redirectioni10n.database.api[ api ] ) {
			Redirectioni10n.WP_API_root = Redirectioni10n.database.api[ api ];
		}

		ev.preventDefault();
		this.setState( { step: 3 } );
	}

	onChange = ev => {
		const state = { [ ev.target.name ]: ev.target.checked };

		if ( ev.target.name === 'log' && ! ev.target.checked ) {
			state.ip = false;
		}

		this.setState( state );
	}

	renderStep0() {
		return (
			<React.Fragment>
				<h2>{ __( 'Welcome to Redirection 🚀🎉' ) }</h2>

				<p>{ __( 'Thank you for installing and using Redirection v%(version)s. This plugin will allow you to manage 301 redirections, keep track of 404 errors, and improve your site, with no knowledge of Apache or Nginx needed.', {
					args: {
						version: Redirectioni10n.version,
					},
				} ) }</p>
				<p>{ __( 'Redirection is designed to be used on sites with a few redirects to sites with thousands of redirects.' ) }</p>

				<h3>{ __( 'How do I use this plugin?' ) }</h3>
				<p>{ __( 'A simple redirect involves setting a {{strong}}source URL{{/strong}} (the old URL) and a {{strong}}target URL{{/strong}} (the new URL). Here\'s an example:', {
					components: {
						strong: <strong />,
					},
				} ) }</p>

				<table className="edit edit-redirection">
					<tbody>
						<tr>
							<th>{ __( 'Source URL' ) }:</th>
							<td><input type="text" readOnly value={ __( '(Example) The source URL is your old or original URL' ) } /></td>
						</tr>
						<tr>
							<th>{ __( 'Target URL' ) }:</th>
							<td><input type="text" readOnly value={ __( '(Example) The target URL is the new URL' ) } /></td>
						</tr>
					</tbody>
				</table>

				<p>{ __( "That's all there is to it - you are now redirecting! Note that the above is just an example - you can now enter a redirect." ) }</p>
				<p>{ __( 'Full documentation can be found on the {{link}}Redirection website.{{/link}}', {
					components: {
						link: <ExternalLink url="https://redirection.me/support/" />,
					},
				} ) }</p>

				<h3>{ __( 'Some features you may find useful are' ) }:</h3>

				<ul>
					<li>{ __( '{{link}}Monitor 404 errors{{/link}}, get detailed information about the visitor, and fix any problems', {
						components: {
							link: <ExternalLink url="https://redirection.me/support/tracking-404-errors/" />,
						},
					} ) }
					</li>
					<li>{ __( '{{link}}Import{{/link}} from .htaccess, CSV, and a variety of other plugins', {
						components: {
							link: <ExternalLink url="https://redirection.me/support/import-export-redirects/" />,
						},
					} ) }</li>
					<li>{ __( 'More powerful URL matching, including {{regular}}regular expressions{{/regular}}, and {{other}}other conditions{{/other}}', {
						components: {
							regular: <ExternalLink url="https://redirection.me/support/redirect-regular-expressions/" />,
							other: <ExternalLink url="https://redirection.me/support/matching-redirects/" />,
						},
					} ) }
					</li>
					<li>{ __( 'Check a URL is being redirected' ) }</li>
				</ul>

				<h3>{ __( "What's next?" ) }</h3>
				<p>{ __( 'First you will be asked a few questions, and then Redirection will set up your database.' ) }</p>
				<p>{ __( 'When ready please press the button to continue.' ) }</p>

				<div className="wizard-buttons">
					<button className="button-primary button" onClick={ this.nextStep }>{ __( 'Start Setup' ) }</button>
				</div>
			</React.Fragment>
		);
	}

	renderStep1() {
		const { monitor, log, ip } = this.state;

		return (
			<React.Fragment>
				<h2>{ __( 'Basic Setup' ) }</h2>

				<p>{ __( 'These are some options you may want to enable now. They can be changed at any time.' ) }</p>

				<div className="wizard-option">
					<p><label><input name="monitor" type="checkbox" checked={ monitor } onChange={ this.onChange } /> { __( 'Monitor permalink changes in WordPress posts and pages' ) }.</label></p>
					<p>
						{ __( 'If you change the permalink in a post or page then Redirection can automatically create a redirect for you.' ) }&nbsp;
						{ __( '{{link}}Read more about this.{{/link}}', {
							components: {
								link: <ExternalLink url="https://redirection.me/support/options/#monitor" />,
							},
						} ) }
					</p>
				</div>

				<div className="wizard-option">
					<p><label><input name="log" type="checkbox" checked={ log } onChange={ this.onChange } /> { __( 'Keep a log of all redirects and 404 errors.' ) }</label></p>
					<p>
						{ __( 'Storing logs for redirects and 404s will allow you to see what is happening on your site. This will increase your database storage requirements.' ) }&nbsp;
						{ __( '{{link}}Read more about this.{{/link}}', {
							components: {
								link: <ExternalLink url="https://redirection.me/support/logs/" />,
							},
						} ) }
					</p>
				</div>

				<div className={ log ? 'wizard-option' : 'wizard-option wizard-option_disabled' }>
					<p><label><input name="ip" type="checkbox" disabled={ ! log } checked={ ip } onChange={ this.onChange } /> { __( 'Store IP information for redirects and 404 errors.' ) }</label></p>
					<p>
						{ __( 'Storing the IP address allows you to perform additional log actions. Note that you will need to adhere to local laws regarding the collection of data (for example GDPR).' ) }&nbsp;
						{ __( '{{link}}Read more about this.{{/link}}', {
							components: {
								link: <ExternalLink url="https://redirection.me/support/privacy-gdpr/" />,
							},
						} ) }
					</p>
				</div>

				<div className="wizard-buttons">
					<button className="button-primary button" onClick={ this.nextStep }>{ __( 'Continue Setup' ) }</button> &nbsp;
					<button className="button" onClick={ this.prevStep }>{ __( 'Go back' ) }</button>
				</div>
			</React.Fragment>
		);
	}

	renderStep2() {
		const { apiTest } = this.props;
		const api = parseUrl.parse( Redirectioni10n.WP_API_root );
		const home = parseUrl.parse( Redirectioni10n.pluginBaseUrl );
		const warning = api.protocol !== home.protocol || api.host !== home.host;
		const routes = restApi();

		return (
			<React.Fragment>
				<h2>{ __( 'REST API' ) }</h2>

				<p>
					{ __( 'Redirection uses the {{link}}WordPress REST API{{/link}} to communicate with WordPress. This is enabled and working by default. Sometimes the REST API is blocked by:', {
						components: {
							link: <ExternalLink url="https://developer.wordpress.org/rest-api/" />,
						},
					} ) }
				</p>

				<ul>
					<li>{ __( 'A security plugin (e.g Wordfence)' ) }</li>
					<li>{ __( 'A server firewall or other server configuration (e.g OVH)' ) }</li>
					<li>{ __( 'Caching software (e.g Cloudflare)' ) }</li>
					<li>{ __( 'Some other plugin that blocks the REST API' ) }</li>
				</ul>

				<p>{ __( 'If you do experience a problem then please consult your plugin documentation, or try contacting your host support. This is generally {{link}}not a problem caused by Redirection{{/link}}.', {
					components: {
						link: <ExternalLink url="https://redirection.me/support/problems/rest-api/" />,
					},
				} ) }</p>

				{ warning && <div className="notice notice-error">
					{ __( 'You have different URLs configured on your WordPress Settings > General page, which is usually an indication of a misconfiguration, and it can cause problems with the REST API. Please review your settings.' ) }
					<p><code>{ api.protocol + '//' + api.host }</code></p>
					<p><code>{ home.protocol + '//' + home.host }</code></p>
				</div> }

				<button className="button wizard-retry" onClick={ this.onRetry } disabled={ this.apiInProgress() }>{ __( 'Retry' ) }</button>

				<h3>{ __( 'Checking your REST API' ) }</h3>
				{ routes.map( ( item, pos ) =>
					<React.Fragment key={ item.value } >
						<ApiResult item={ item } result={ apiTest[ item.value ] } method="GET" alt={ pos % 2 === 1 } />
						<ApiResult item={ item } result={ apiTest[ item.value ] } method="POST" alt={ pos % 2 === 1 } />
					</React.Fragment>
				) }

				<p>{ __( 'You need at least one GET/POST pair for the plugin to work.' ) }</p>

				<div className="wizard-buttons">
					<button className="button-primary button" onClick={ this.finishSetup }>{ __( 'Finish Setup' ) }</button> &nbsp;
					<button className="button" onClick={ this.prevStep }>{ __( 'Go back' ) }</button>
				</div>
			</React.Fragment>
		);
	}

	renderStep3() {
		return <Database onFinished={ this.afterFinishInstall } />;
	}

	afterFinishInstall = () => {
		const { ip, log, monitor } = this.state;

		this.props.onSaveSettings( {
			expire_redirect: log ? 7 : -1,
			expire_404: log ? 7 : -1,
			ip_logging: ip ? 1 : 0,
			rest_api: this.getFirstApi(),
			monitor_types: monitor ? [ 'post', 'page' ] : undefined,
			monitor_post: monitor ? 1 : 0,
		} );
	}

	getContentForStep( step ) {
		if ( step === 3 ) {
			return this.renderStep3();
		} if ( step === 2 ) {
			return this.renderStep2();
		} else if ( step === 1 ) {
			return this.renderStep1();
		}

		return this.renderStep0();
	}

	render() {
		const { step } = this.state;
		const { result } = this.props;
		const content = this.getContentForStep( step );

		return (
			<React.Fragment>
				{ result === STATUS_FAILED && <Error /> }

				<div className="wizard-wrapper">
					{ step !== 0 && step !== 3 && <h1>{ __( 'Redirection' ) }</h1> }

					<div className="wizard">
						{ content }
					</div>
				</div>

				<div className="wizard-support">
					<ExternalLink url="https://redirection.me/contact/">{ __( 'I need some support!' ) }</ExternalLink>
				</div>
			</React.Fragment>
		);
	}
}

function mapStateToProps( state ) {
	const { result } = state.settings.database;
	const { apiTest } = state.settings;

	return {
		result,
		apiTest,
	};
}

function mapDispatchToProps( dispatch ) {
	return {
		onCheckApi: api => {
			dispatch( checkApi( api ) );
		},
		onSaveSettings: settings => {
			dispatch( saveSettings( settings ) );
		},
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( WelcomeWizard );
