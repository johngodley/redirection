/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import classnames from 'classnames';
import PropTypes from 'prop-types';
import { translate as __ } from 'lib/locale';

class ErrorNotice extends React.Component {
	constructor( props ) {
		super( props );

		this.state = { visible: true };
		this.onClick = this.dismiss.bind( this );
		window.scrollTo( 0, 0 );
	}

	dismiss() {
		this.setState( { visible: false } );
	}

	getDebug( error ) {
		const message = [
			'Versions: ' + Redirectioni10n.versions,
			'Nonce: ' + Redirectioni10n.WP_API_nonce,
			'Last Action: ' + Redirectioni10n.failedAction,
			'Last Data: ' + JSON.stringify( Redirectioni10n.failedData ),
			'Error: ' + error,
		];

		return message;
	}

	render() {
		const { message } = this.props;
		const debug = this.getDebug( message );
		const classes = classnames( {
			notice: true,
			'notice-error': true,
			'is-dismiss': true,
		} );

		if ( ! this.state.visible ) {
			return false;
		}

		return (
			<div className={ classes }>
				<div className="closer" onClick={ this.onClick }>&#10006;</div>
				<h1>{ __( 'Something went wrong 🙁' ) }</h1>
				<p>{ __( 'I was trying to do a thing and it went wrong. It may be a temporary issue and if you try again it could work - great!' ) }</p>

				<h2>{ __( "It didn't work when I tried again" ) }</h2>
				<p>{ __( 'See if your problem is described on the list of outstanding {{link}}Redirection issues{{/link}}. Please add more details if you find the same problem.', {
					components: {
						link: <a target="_blank" rel="noopener noreferrer" href="https://github.com/johngodley/redirection/issues" />
					}
				} ) }</p>
				<p>{ __( "If the issue isn't known then try disabling other plugins - it's easy to do, and you can re-enable them quickly. Other plugins can sometimes cause conflicts, and knowing this in advance will help a lot." ) }</p>
				<p>{ __( 'If this is a new problem then please either create a new issue, or send it directly to john@urbangiraffe.com. Include a description of what you were trying to do and the important details listed below. If you can include a screenshot then even better.' ) }</p>

				<h2>{ __( 'Important details for the thing you just did' ) }</h2>
				<p>{ __( 'Please include these details in your report' ) }:</p>
				<p><textarea readOnly={ true } rows={ debug.length } cols="120" value={ debug.join( '\n' ) } spellCheck={ false }></textarea></p>
			</div>
		);
	}
}

ErrorNotice.propTypes = {
	message: PropTypes.string.isRequired,
};

export default ErrorNotice;
