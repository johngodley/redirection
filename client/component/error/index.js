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
			'Versions: ' + Redirectioni10n.versions,
			'Nonce: ' + Redirectioni10n.WP_API_nonce,
		];

		for ( let x = 0; x < errors.length; x++ ) {
			message.push( '' );
			message.push( 'Action: ' + errors[ x ].action );

			if ( errors[ x ].data !== '""' ) {
				message.push( 'Params: ' + errors[ x ].data );
			}

			message.push( 'Code: ' + errors[ x ].code );
			message.push( 'Error: ' + errors[ x ].error );
			message.push( 'Raw: ' + errors[ x ].response );
		}

		return message;
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
				<p>{ __( 'I was trying to do a thing and it went wrong. It may be a temporary issue and if you try again it might work - great!' ) }</p>

				<h3>{ __( "It didn't work when I tried again" ) }</h3>
				<p>{ __( 'See if your problem is described on the list of outstanding {{link}}Redirection issues{{/link}}. Please add more details if you find the same problem.', {
					components: {
						link: <a target="_blank" rel="noopener noreferrer" href="https://github.com/johngodley/redirection/issues" />
					}
				} ) }</p>
				<p>{ __( "If the issue isn't known then try disabling other plugins - it's easy to do, and you can re-enable them quickly. Other plugins can sometimes cause conflicts, and knowing this in advance will help a lot." ) }</p>
				<p>
					{ __( 'If this is a new problem then please either {{strong}}create a new issue{{/strong}} or send it in an {{strong}}email{{/strong}}. Include a description of what you were trying to do and the important details listed below. Please include a screenshot.', {
						components: {
							strong: <strong />,
						}
					} ) }</p>

				<p><a href={ github } className="button-primary">{ __( 'Create Issue' ) }</a> <a href={ email } className="button-secondary">{ __( 'Email' ) }</a></p>

				<h3>{ __( 'Important details' ) }</h3>
				<p>{ __( 'Include these details in your report' ) }:</p>
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
