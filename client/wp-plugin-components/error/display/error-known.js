/**
 * External dependencies
 */

import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */

import ErrorDebug from '../debug';
import DecodeError from '../decode-error';

/**
 *
 * @param {object} props
 * @param {string} props.title
 * @param {boolean} props.mini
 */
function DisplayKnownError( props ) {
	const { title, children, error, links, locale } = props;

	return (
		<>
			<h2>{ title || __( 'Something went wrong 🙁', locale ) }</h2>

			<div className="wpl-error__detail">
				<DecodeError error={ error } links={ links } locale={ locale } />
			</div>

			{ children }

			<ErrorDebug { ...props } />
		</>
	);
}

export default DisplayKnownError;
