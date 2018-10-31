/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import classnames from 'classnames';
import { connect } from 'react-redux';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */

import Select from 'component/select';
import { clearErrors } from 'state/message/action';
import { restApi } from 'page/options/options-form';
import './style.scss';

class Error extends React.Component {
	constructor( props ) {
		super( props );

		this.onClick = this.dismiss.bind( this );
		this.state = {
			rest_api: Redirectioni10n.api_setting,
		};
	}

	componentDidUpdate( prevProps ) {
		if ( prevProps.errors.length === 0 && this.props.errors.length > 0 ) {
			window.scrollTo( 0, 0 );
		}
	}

	dismiss() {
		this.props.onClear();
	}

	getDebug( errors ) {
		const message = [
			Redirectioni10n.versions,
		];

		for ( let x = 0; x < errors.length; x++ ) {
			const { request = false } = errors[ x ];

			message.push( '' );
			message.push( 'Error: ' + this.getErrorDetails( errors[ x ] ) );

			if ( request && request.status && request.statusText ) {
				message.push( 'Action: ' + request.action );

				if ( request.params ) {
					message.push( 'Params: ' + JSON.stringify( request.params ) );
				}

				message.push( 'Code: ' + request.status + ' ' + request.statusText );
			}

			if ( request ) {
				message.push( 'Raw: ' + ( request.raw ? request.raw : '-no data-' ) );
			}
		}

		return message;
	}

	getErrorDetailsTitle( error ) {
		if ( error.code === 0 ) {
			return error.message;
		}

		if ( error.data && error.data.wpdb ) {
			return <span>{ `${ error.message } (${ error.code })` }: <code>{ error.data.wpdb }</code></span>;
		}

		if ( error.code ) {
			return `${ error.message } (${ error.code })`;
		}

		return error.message;
	}

	getErrorDetails( error ) {
		if ( error.code === 0 ) {
			return error.message;
		}

		if ( error.data && error.data.wpdb ) {
			return `${ error.message } (${ error.code }): ${ error.data.wpdb }`;
		}

		if ( error.code ) {
			return `${ error.message } (${ error.code })`;
		}

		return error.message;
	}

	getErrorMessage( errors ) {
		console.log( errors );

		const messages = errors.map( item => {
			if ( item.action && item.action === 'reload' ) {
				if ( document.location.search.indexOf( 'retry=' ) === -1 ) {
					document.location.href += '&retry=1';
					return;
				}

				return __( 'The data on this page has expired, please reload.' );
			}

			if ( item.code === 0 ) {
				return __( 'WordPress did not return a response. This could mean an error occurred or that the request was blocked. Please check your server error_log.' );
			}

			if ( item.code === 'rest_cookie_invalid_nonce' ) {
				return __( 'Please logout and login again.' );
			}

			if ( item.request && item.request.status === 403 ) {
				return __( 'Your server returned a 403 Forbidden error which may indicate the request was blocked. Are you using a firewall or a security plugin like mod_security?' );
			}

			if ( item.request && item.request.status === 413 ) {
				return __( 'Your server has rejected the request for being too big. You will need to change it to continue.' );
			}

			if ( item.code === 'disabled' || item.code === 'rest_disabled' ) {
				return __( 'Your WordPress REST API has been disabled. You will need to enable it for Redirection to continue working' );
			}

			if ( item.message.indexOf( 'Unexpected token' ) !== -1 ) {
				return __( 'WordPress returned an unexpected message. This could be caused by your REST API not working, or by another plugin or theme.' );
			}

			if ( item.message ) {
				return this.getErrorDetailsTitle( item );
			}

			return __( 'I was trying to do a thing and it went wrong. It may be a temporary issue and if you try again it might work - great!' );
		} );

		return <p>{ Object.keys( [ {} ].concat( messages ).reduce( ( l, r ) => l[ r ] = l ) ) }</p>;
	}

	onChange = ev => {
		this.setState( { rest_api: ev.target.value } );
	}

	getHeight( rows ) {
		let height = 0;

		for ( let index = 0; index < rows.length; index++ ) {
			const parts = rows[ index ].split( '\n' );

			height += parts.length;
		}

		return Math.max( height, 20 );
	}

	renderError( errors ) {
		const debug = this.getDebug( errors );
		const classes = classnames( {
			notice: true,
			'notice-error': true,
		} );
		const email = 'mailto:john@redirection.me?subject=Redirection%20Error&body=' + encodeURIComponent( debug.join( '\n' ) );
		const github = 'https://github.com/johngodley/redirection/issues/new?title=Redirection%20Error&body=' + encodeURIComponent( '```\n' + debug.join( '\n' ) + '\n```\n\n' );

		return (
			<div className={ classes }>
				<div className="closer" onClick={ this.onClick }>&#10006;</div>
				<h2>{ __( 'Something went wrong 🙁' ) }</h2>

				<strong>{ this.getErrorMessage( errors ) }</strong>

				<ol>
					<li>
						{ __( 'If you are unable to get anything working then Redirection may have difficulty communicating with your server. You can try manually changing this setting:' ) }
						<form action={ Redirectioni10n.pluginRoot + '&sub=support' } method="POST">
							REST API: <Select items={ restApi() } name="rest_api" value={ this.state.rest_api } onChange={ this.onChange } />

							<input type="submit" className="button-secondary" value={ __( 'Save' ) } />
							<input type="hidden" name="_wpnonce" value={ Redirectioni10n.WP_API_nonce } />
							<input type="hidden" name="action" value="rest_api" />
						</form>
					</li>
					<li>
						{ __( 'Take a look at the {{link}}plugin status{{/link}}. It may be able to identify and "magic fix" the problem.', {
							components: {
								link: <a href="?page=redirection.php&sub=support" />,
							},
						} ) }
					</li>
					<li>
						{ __( '{{link}}Redirection is unable to talk to your REST API{{/link}}. If you have disabled it then you will need to enable it.', {
							components: {
								link: <a target="_blank" rel="noreferrer noopener" href="https://redirection.me/support/problems/rest-api/?utm_source=redirection&utm_medium=plugin&utm_campaign=support" />,
							},
						} ) }
					</li>
					<li>
						{ __( '{{link}}Security software may be blocking Redirection{{/link}}. You will need to configure this to allow REST API requests.', {
							components: {
								link: <a target="_blank" rel="noreferrer noopener" href="https://redirection.me/support/problems/security-software/?utm_source=redirection&utm_medium=plugin&utm_campaign=support" />,
							},
						} ) }
					</li>
					<li>
						{ __( '{{link}}Caching software{{/link}}, in particular Cloudflare, can cache the wrong thing. Try clearing all your caches.', {
							components: {
								link: <a target="_blank" rel="noreferrer noopener" href="https://redirection.me/support/problems/cloudflare/?utm_source=redirection&utm_medium=plugin&utm_campaign=support" />,
							},
						} ) }
					</li>
					<li>
						{ __( '{{link}}Please temporarily disable other plugins!{{/link}} This fixes so many problems.', {
							components: {
								link: <a target="_blank" rel="noreferrer noopener" href="https://redirection.me/support/problems/plugins/?utm_source=redirection&utm_medium=plugin&utm_campaign=support" />,
							},
						} ) }
					</li>
				</ol>

				<h3>{ __( 'None of the suggestions helped' ) }</h3>
				<p>
					{ __( 'If this is a new problem then please either {{strong}}create a new issue{{/strong}} or send it in an {{strong}}email{{/strong}}. Include a description of what you were trying to do and the important details listed below. Please include a screenshot.', {
						components: {
							strong: <strong />,
						},
					} ) }
				</p>

				<p><a href={ github } className="button-primary">{ __( 'Create Issue' ) }</a> <a href={ email } className="button-secondary">{ __( 'Email' ) }</a></p>

				<h3>{ __( 'Important details' ) }</h3>
				<p>{ __( 'Include these details in your report {{strong}}along with a description of what you were doing{{/strong}}.', {
					components: {
						strong: <strong />,
					},
				} ) }</p>
				<p><textarea readOnly={ true } rows={ this.getHeight( debug ) } cols="120" value={ debug.join( '\n' ) } spellCheck={ false }></textarea></p>
			</div>
		);
	}

	render() {
		const { errors } = this.props;

		if ( errors.length === 0 ) {
			return null;
		}

		return this.renderError( errors );
	}
}

function mapStateToProps( state ) {
	const { errors } = state.message;

	return {
		errors,
	};
}

function mapDispatchToProps( dispatch ) {
	return {
		onClear: () => {
			dispatch( clearErrors() );
		},
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps,
)( Error );
