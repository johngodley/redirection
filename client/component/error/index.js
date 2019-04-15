/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import TextareaAutosize from 'react-textarea-autosize';
import { connect } from 'react-redux';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */

import ExternalLink from 'component/external-link';
import RestApiStatus from 'component/rest-api-status';
import DecodeError from 'component/decode-error';
import { clearErrors } from 'state/message/action';
import './style.scss';

class Error extends React.Component {
	componentDidUpdate( prevProps ) {
		if ( prevProps.errors.length === 0 && this.props.errors.length > 0 ) {
			window.scrollTo( 0, 0 );
		}
	}

	onClick = () => {
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

	removeSameError( errors ) {
		return errors.filter( ( item, index ) => {
			for ( let pos = index + 1; index < errors.length - 1; index++ ) {
				if ( item.code && errors[ pos ].code && item.code === errors[ pos ].code ) {
					return false;
				}

				if ( item.message && errors[ pos ].message && item.message === errors[ pos ].message ) {
					return false;
				}
			}

			return true;
		} );
	}

	renderError( errors ) {
		const uniqErrors = this.removeSameError( errors );
		const debug = this.getDebug( uniqErrors );
		const email = 'mailto:john@redirection.me?subject=Redirection%20Error&body=' + encodeURIComponent( debug.join( '\n' ) );
		const github = 'https://github.com/johngodley/redirection/issues/new?title=Redirection%20Error&body=' + encodeURIComponent( '```\n' + debug.join( '\n' ) + '\n```\n\n' );

		return (
			<div className="red-error">
				<div className="closer" onClick={ this.onClick }>&#10006;</div>
				<h2>{ __( 'Something went wrong 🙁' ) }</h2>

				<div className="red-error_title">
					{ uniqErrors.map( ( error, pos ) => <DecodeError error={ error } key={ pos } /> ) }
				</div>

				<RestApiStatus />

				<h3>{ __( 'What do I do next?' ) }</h3>

				<ol>
					<li>
						{ __( 'Take a look at the {{link}}plugin status{{/link}}. It may be able to identify and "magic fix" the problem.', {
							components: {
								link: <a href="?page=redirection.php&sub=support" />,
							},
						} ) }
					</li>
					<li>
						{ __( '{{link}}Caching software{{/link}}, in particular Cloudflare, can cache the wrong thing. Try clearing all your caches.', {
							components: {
								link: <ExternalLink url="https://redirection.me/support/problems/cloudflare/" />,
							},
						} ) }
					</li>
					<li>
						{ __( '{{link}}Please temporarily disable other plugins!{{/link}} This fixes so many problems.', {
							components: {
								link: <ExternalLink url="https://redirection.me/support/problems/plugins/" />,
							},
						} ) }
					</li>
				</ol>

				<h3>{ __( 'That didn\'t help' ) }</h3>

				<p>
					{ __( 'Please {{strong}}create an issue{{/strong}} or send it in an {{strong}}email{{/strong}}.', {
						components: {
							strong: <strong />,
						},
					} ) }
				</p>

				<p><a href={ github } className="button-primary">{ __( 'Create An Issue' ) }</a> <a href={ email } className="button-secondary">{ __( 'Email' ) }</a></p>
				<p>{ __( 'Include these details in your report along with a description of what you were doing and a screenshot' ) }</p>

				<p><TextareaAutosize readOnly={ true } cols="120" value={ debug.join( '\n' ) } spellCheck={ false } /></p>
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
	const { message: { errors } } = state;

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
