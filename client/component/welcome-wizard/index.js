/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import { connect } from 'react-redux';
import { translate as __ } from 'i18n-calypso';
import * as parseUrl from 'url';

/**
 * Internal dependencies
 */
import ExternalLink from 'wp-plugin-components/external-link';
import Database from 'component/database';
import RestApiStatus from 'component/rest-api-status';
import getErrorLinks from 'lib/error-links';
//import { getApiUrl, setApiUrl } from 'lib/api';
import { saveSettings, finishUpgrade } from 'state/settings/action';
import { pluginImport } from 'state/io/action';
import { STATUS_FAILED } from 'state/settings/type';
import './style.scss';

const IMPORTER_WP = 'wordpress-old-slugs';

const STEP_WELCOME = 0;
const STEP_BASIC = 1;
const STEP_API = 2;
const STEP_DATABASE = 3;
const STEP_IMPORT = 4;
const STEP_IMPORTING = 5;

class WelcomeWizard extends React.Component {
	constructor( props ) {
		super( props );

		this.state = {
			step: STEP_WELCOME,
			monitor: false,
			log: false,
			ip: false,
			manual: false,
			importers: props.importers.find( item => item.id === IMPORTER_WP ) ? [ IMPORTER_WP ] : [],
		};
	}

	nextStep = ev => {
		const nextStage = this.state.step + 1;

		ev.preventDefault();
		this.performActionForStep( nextStage );
		this.setState( { step: nextStage } );
	}

	prevStep = ev => {
		const nextStage = this.state.step - 1;

		ev.preventDefault();
		this.performActionForStep( nextStage );
		this.setState( { step: nextStage } );
	}

	// Returns the best API route (the one with a valid GET and POST), in order they are defined. If nothing is valid, return the default
	getFirstApi() {
		const { apiTest } = this.props;
		const keys = Object.keys( apiTest );

		for ( let index = 0; index < keys.length; index++ ) {
			const key = keys[ index ];

			if ( apiTest[ key ] && apiTest[ key ].GET.status === 'ok' && apiTest[ key ].POST.status === 'ok' ) {
				return key;
			}
		}

		return 0;
	}

	startManual = ev => {
		ev.preventDefault();
		this.saveSettings();
		this.setState( { manual: true } );
	}

	stopManual = ev => {
		ev.preventDefault();
		this.setState( { manual: false } );
	}

	saveSettings() {
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

	afterFinishInstall = () => {
		this.saveSettings();

		if ( this.props.importers.length > 0 ) {
			// We have potential redirects to import - show the import stage
			this.setState( { step: STEP_IMPORT, manual: false } );
		} else {
			this.props.onFinishInstall();
		}
	}

	performActionForStep = stage => {
		if ( stage === STEP_DATABASE ) {
			// Set the API to the best
			const api = this.getFirstApi();

			// Set our REST API route
			if ( Redirectioni10n.api.routes[ api ] ) {
				setApiUrl( Redirectioni10n.api.routes[ api ] );
			}
		} else if ( stage === STEP_IMPORTING ) {
			if ( this.state.importers.length > 0 ) {
				// Process the importers
				this.props.onImport( this.state.importers );
			} else {
				// Nothing to import - just finish
				this.props.onFinishInstall();
			}
		}
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
			<>
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

				<table className="redirect-edit">
					<tbody>
						<tr>
							<th>{ __( 'Source URL' ) }:</th>
							<td><input type="text" className="regular-text" readOnly value={ __( '(Example) The source URL is your old or original URL' ) } /></td>
						</tr>
						<tr>
							<th>{ __( 'Target URL' ) }:</th>
							<td><input type="text" className="regular-text" readOnly value={ __( '(Example) The target URL is the new URL' ) } /></td>
						</tr>
					</tbody>
				</table>

				<p>{ __( "That's all there is to it - you are now redirecting! Note that the above is just an example." ) }</p>
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
			</>
		);
	}

	renderStep1() {
		const { monitor, log, ip } = this.state;

		return (
			<>
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
			</>
		);
	}

	renderStep2() {
		const api = parseUrl.parse( getApiUrl() );
		const home = parseUrl.parse( Redirectioni10n.pluginBaseUrl );
		const warning = api.protocol !== home.protocol || api.host !== home.host;

		return (
			<>
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

				{ warning && <div className="wpl-error">
					{ __( 'You have different URLs configured on your WordPress Settings > General page, which is usually an indication of a misconfiguration, and it can cause problems with the REST API. Please review your settings.' ) }
					<p><code>{ api.protocol + '//' + api.host }</code></p>
					<p><code>{ home.protocol + '//' + home.host }</code></p>
				</div> }

				<RestApiStatus allowChange={ false } />

				<p>{ __( 'You will need at least one working REST API to continue.' ) }</p>

				<div className="wizard-buttons">
					<button className="button-primary button" onClick={ this.nextStep }>{ __( 'Finish Setup' ) }</button> &nbsp;
					<button className="button" onClick={ this.prevStep }>{ __( 'Go back' ) }</button>
				</div>
			</>
		);
	}

	renderStep3() {
		return <Database onFinished={ this.afterFinishInstall } manual={ this.state.manual } />;
	}

	renderStep4() {
		const { importers } = this.state;
		const wpImport = this.props.importers.find( item => item.id === IMPORTER_WP );
		const otherImporters = this.props.importers.filter( item => item.id !== IMPORTER_WP );

		return (
			<div>
				<h2>{ __( 'Import Existing Redirects' ) }</h2>

				<p>{ __( 'Importing existing redirects from WordPress or other plugins is a good way to get started with Redirection. Check each set of redirects you wish to import.' ) }</p>

				{ wpImport && (
					<>
						<p>{ __( 'WordPress automatically creates redirects when you change a post URL. Importing these into Redirection will allow you to manage and monitor them.' ) }</p>
						<ul>
							<li>
								<label><input type="checkbox" name={ IMPORTER_WP } onChange={ this.onImporter } checked={ importers.indexOf( IMPORTER_WP ) !== -1 } /> { wpImport.name } ({ wpImport.total })</label>
							</li>
						</ul>
					</>
				) }

				{ otherImporters.length > 0 && (
					<>
						<p>{ __( 'The following plugins have been detected.' ) }</p>
						<ul>
							{ otherImporters.map( item => (
								<li key={ item.id }>
									<label>
										<input type="checkbox" name={ item.id } onChange={ this.onImporter } checked={ importers.indexOf( item.id ) !== -1 } /> { item.name } ({ item.total })
									</label>
								</li>
							) ) }
						</ul>
					</>
				) }

				<div className="wizard-buttons">
					<button className="button-primary button" onClick={ this.nextStep }>{ __( 'Continue' ) }</button>
				</div>
			</div>
		);
	}

	renderStep5() {
		return (
			<div>
				<h2>{ __( 'Import Existing Redirects' ) }</h2>

				<p>{ __( 'Please wait, importing.' ) }</p>

				<div className="loader-wrapper loader-textarea">
					<div className="wpl-placeholder__loading"></div>
				</div>
			</div>
		);
	}

	onImporter = ( { target } ) => {
		const { importers } = this.state;
		const changed = target.checked ? importers.concat( target.name ) : importers.filter( item => item !== target.name );

		this.setState( { importers: changed } );
	}

	getContentForStep( step ) {
		if ( step === STEP_IMPORT ) {
			return this.renderStep4();
		} else if ( step === STEP_DATABASE ) {
			return this.renderStep3();
		} else if ( step === STEP_API ) {
			return this.renderStep2();
		} else if ( step === STEP_BASIC ) {
			return this.renderStep1();
		} else if ( step === STEP_IMPORTING ) {
			return this.renderStep5();
		}

		return this.renderStep0();
	}

	render() {
		const { step, manual } = this.state;
		const { result } = this.props;
		const content = this.getContentForStep( step );

		return (
			<>
				{ result === STATUS_FAILED && <Error links={ getErrorLinks() } /> }

				<div className="wizard-wrapper">
					{ step !== 0 && step !== 3 && <h1>{ __( 'Redirection' ) }</h1> }

					<div className="wizard">
						{ content }
					</div>
				</div>

				<div className="wizard-support">
					<ExternalLink url="https://redirection.me/contact/">{ __( 'I need support!' ) }</ExternalLink>
					{ step === 2 && <> | <a href="#" onClick={ this.startManual }>{ __( 'Manual Install' ) }</a></>}
					{ step === 3 && manual && <> | <a href="#" onClick={ this.stopManual }>{ __( 'Automatic Install' ) }</a></>}
				</div>
			</>
		);
	}
}

function mapStateToProps( state ) {
	const { result } = state.settings.database;
	const { apiTest } = state.settings;
	const { importers, importingStatus } = state.io;

	return {
		result,
		apiTest,
		importers,
		importingStatus,
	};
}

function mapDispatchToProps( dispatch ) {
	return {
		onSaveSettings: settings => {
			dispatch( saveSettings( settings ) );
		},
		onImport: importers => {
			dispatch( pluginImport( importers ) );
		},
		onFinishInstall: () => {
			dispatch( finishUpgrade() );
		},
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( WelcomeWizard );
