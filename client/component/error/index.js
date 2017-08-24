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

import { clearErrors } from 'state/message/action';

class Error extends React.Component {
	constructor( props ) {
		super( props );

		this.onClick = this.dismiss.bind( this );
	}

	componentWillUpdate( nextProps ) {
		if ( nextProps.errors.length > 0 && this.props.errors.length === 0 ) {
			window.scrollTo( 0, 0 );
		}
	}

	dismiss() {
		this.props.onClear();
	}

	getDebug( errors ) {
		const message = [
			Redirectioni10n.versions,
			'Nonce: ' + Redirectioni10n.WP_API_nonce,
			'URL: ' + Redirectioni10n.WP_API_root.replace( /\/\/.*?\//, '//<site>/' ),
		];

		for ( let x = 0; x < errors.length; x++ ) {
			const { request = false } = errors[ x ];

			message.push( '' );
			message.push( 'Error: ' + this.getErrorDetails( errors[ x ] ) );

			if ( request ) {
				message.push( 'Action: ' + request.action );

				if ( request.params ) {
					message.push( 'Params: ' + JSON.stringify( request.params ) );
				}

				message.push( 'Code: ' + request.status + ' ' + request.statusText );
				message.push( 'Raw: ' + ( request.raw ? request.raw : '-no data-' ) );
			}
		}

		return message;
	}

	getErrorDetailsTitle( error ) {
		if ( error.code === 0 ) {
			return error.message;
		}

		if ( error.wpdb ) {
			return <span>{ `${ error.message } (${ error.code })` }: <code>{ error.wpdb }</code></span>;
		}

		return `${ error.message } (${ error.code })`;
	}

	getErrorDetails( error ) {
		if ( error.code === 0 ) {
			return error.message;
		}

		if ( error.wpdb ) {
			return `${ error.message } (${ error.code }): ${ error.wpdb }`;
		}

		return `${ error.message } (${ error.code })`;
	}

	getErrorMessage( errors ) {
		const messages = errors.map( item => {
			if ( item.action && item.action === 'reload' ) {
				return __( 'The data on this page has expired, please reload.' );
			}

			if ( item.code === 0 ) {
				return __( 'WordPress did not return a response. This could mean an error occurred or that the request was blocked. Please check your server error_log.' );
			}

			if ( item.request.status === 403 ) {
				return __( 'Your server returned a 403 Forbidden error which may indicate the request was blocked. Are you using a firewall or a security plugin?' );
			}

			if ( item.message.indexOf( 'Unexpected token' ) !== -1 ) {
				return __( "WordPress returned an unexpected message. This usually indicates that a plugin or theme is outputting data when it shouldn't be. Please try disabling other plugins and try again." );
			}

			if ( item.message ) {
				return this.getErrorDetailsTitle( item );
			}

			return __( 'I was trying to do a thing and it went wrong. It may be a temporary issue and if you try again it might work - great!' );
		} );

		return <p>{ Object.keys( [ {} ].concat( messages ).reduce( ( l, r ) => l[ r ] = l ) ) }</p>;
	}

	renderError( errors ) {
		const debug = this.getDebug( errors );
		const classes = classnames( {
			notice: true,
			'notice-error': true,
		} );
		const email = 'mailto:john@urbangiraffe.com?subject=Redirection%20Error&body=' + encodeURIComponent( debug.join( '\n' ) );
		const github = 'https://github.com/johngodley/redirection/issues/new?title=Redirection%20Error&body=' + encodeURIComponent( '```\n' + debug.join( '\n' ) + '\n```\n\n' );

		return (
			<div className={ classes }>
				<div className="closer" onClick={ this.onClick }>&#10006;</div>
				<h2>{ __( 'Something went wrong 🙁' ) }</h2>
				{ this.getErrorMessage( errors ) }

				<h3>{ __( "It didn't work when I tried again" ) }</h3>
				<p>{ __( 'See if your problem is described on the list of outstanding {{link}}Redirection issues{{/link}}. Please add more details if you find the same problem.', {
					components: {
						link: <a target="_blank" rel="noopener noreferrer" href="https://github.com/johngodley/redirection/issues" />
					}
				} ) }</p>
				<p>{ __( "If the issue isn't known then try disabling other plugins - it's easy to do, and you can re-enable them quickly. Other plugins can sometimes cause conflicts." ) }</p>
				<p>
					{ __( 'If this is a new problem then please either {{strong}}create a new issue{{/strong}} or send it in an {{strong}}email{{/strong}}. Include a description of what you were trying to do and the important details listed below. Please include a screenshot.', {
						components: {
							strong: <strong />,
						}
					} ) }</p>

				<p><a href={ github } className="button-primary">{ __( 'Create Issue' ) }</a> <a href={ email } className="button-secondary">{ __( 'Email' ) }</a></p>

				<h3>{ __( 'Important details' ) }</h3>
				<p>{ __( 'Include these details in your report {{strong}}along with a description of what you were doing{{/strong}}.', {
					components: {
						strong: <strong />,
					}
				} ) }</p>
				<p><textarea readOnly={ true } rows={ debug.length + 2 } cols="120" value={ debug.join( '\n' ) } spellCheck={ false }></textarea></p>
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
