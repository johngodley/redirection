/**
 * External dependencies
 */

import { useState } from 'react';
import TextareaAutosize from 'react-textarea-autosize';
import { __ } from '@wordpress/i18n';

function getErrorDetails( error ) {
	if ( typeof error === 'string' ) {
		return error;
	}

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

function getDebug( error, versions, context ) {
	const message = versions ? [ versions ] : [];
	const { request = false, data } = error;

	message.push( '' );

	const { apiFetch } = request;
	if ( apiFetch && apiFetch.status && apiFetch.statusText ) {
		message.push( 'Action: ' + apiFetch.action );

		if ( apiFetch.body && apiFetch.body !== '{}' ) {
			message.push( 'Params: ' + apiFetch.body );
		}

		message.push( 'Code: ' + apiFetch.status + ' ' + apiFetch.statusText );
		message.push( '' );
	}

	message.push( 'Error: ' + getErrorDetails( error ) );

	if ( data ) {
		message.push( 'Raw: ' + data );
	}

	if ( context ) {
		message.push( '' );
		message.push( 'Context:' );
		message.push( context );
	}

	return message;
}

function ErrorDebug( props ) {
	const { error, mini, context, renderDebug, versions, noParse = false, details = [], locale } = props;
	const [ showDebug, setShowDebug ] = useState( ! mini );

	if ( ! showDebug ) {
		return (
			<p>
				<button className="button button-secondary" type="button" onClick={ () => setShowDebug( true ) }>
					{ __( 'Show debug', locale ) }
				</button>
			</p>
		);
	}

	const debug = noParse ? [ error ] : getDebug( error, versions, context );

	return (
		<>
			<h3>{ __( 'Debug Information', locale ) }</h3>

			{ renderDebug && renderDebug( details.concat( debug ).join( '\n' ) ) }

			<p>
				<TextareaAutosize
					readOnly
					cols={ 120 }
					value={ details.concat( debug ).join( '\n' ) }
					maxRows={ 40 }
					spellCheck={ false }
				/>
			</p>
		</>
	);
}

export default ErrorDebug;
