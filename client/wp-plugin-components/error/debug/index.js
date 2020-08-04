/**
 * External dependencies
 */

import React, { useState } from 'react';
import TextareaAutosize from 'react-textarea-autosize';
import { translate as __ } from 'wp-plugin-lib/locale';

function getErrorDetails( error ) {
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

function getDebug( errors, versions, context ) {
	const message = versions ? [ versions ] : [];

	for ( let x = 0; x < errors.length; x++ ) {
		const { request = false, data } = errors[ x ];

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

		message.push( 'Error: ' + getErrorDetails( errors[ x ] ) );

		if ( data ) {
			message.push( 'Raw: ' + data );
		}
	}

	if ( context ) {
		message.push( '' );
		message.push( 'Context:' );
		message.push( context );
	}

	return message;
}

function ErrorDebug( props ) {
	const { errors, mini, context, renderDebug, versions, noParse = false } = props;
	const [ showDebug, setShowDebug ] = useState( ! mini );

	if ( ! showDebug ) {
		return (
			<p>
				<button className="button button-secondary" type="button" onClick={ () => setShowDebug( true ) }>
					{ __( 'Show debug' ) }
				</button>
			</p>
		);
	}

	const debug = noParse ? errors : getDebug( errors, versions, context );

	return (
		<>
			<h3>{ __( 'Debug Information' ) }</h3>

			{ renderDebug && renderDebug( debug.join( '\n' ) ) }

			<p>
				<TextareaAutosize
					readOnly
					cols={ 120 }
					value={ debug.join( '\n' ) }
					spellCheck={ false }
				/>
			</p>
		</>
	);
}

export default ErrorDebug;
