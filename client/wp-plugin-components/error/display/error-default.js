/**
 * External dependencies
 */

import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */

import ErrorDebug from '../debug';
import DecodeError, { shouldHideDebug, shouldShowInformation } from '../decode-error';
import { is404 } from '../decode-error/error-detect';

function getTitle( error, title, locale ) {
	if ( is404( error ) ) {
		return __( 'REST API 404');
	}

	return title || __( 'Something went wrong 🙁', locale )
}

function DisplayDefaultError( props ) {
	const { title, children, error, links, locale } = props;
	const showInfo = shouldShowInformation( error );
	const hideDebug = shouldHideDebug( error );
	const showSupport = ! is404( error );

	return (
		<>
			<h2>{ getTitle( error, title, locale ) }</h2>

			<div className="wpl-error__title">
				<DecodeError error={ error } links={ links } locale={ locale } />
			</div>

			{ showInfo && children }

			<ErrorDebug { ...props } { ...( hideDebug ? { mini: true } : {} ) } renderDebug={ showSupport ? null : props.renderDebug } />
		</>
	);
}

export default DisplayDefaultError;
