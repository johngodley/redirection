/**
 * External dependencies
 */

import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */

import ErrorDebug from '../debug';

function DisplayFixedError( props ) {
	const { title, children, locale } = props;

	return (
		<>
			<h2>{ title || __( 'Something went wrong 🙁', locale ) }</h2>

			{ children }

			<ErrorDebug { ...props } noParse />
		</>
	);
}

export default DisplayFixedError;
