/**
 * External dependencies
 */

import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */

import ErrorDebug from '../debug';

function DisplayNonceError( props ) {
	const { locale } = props;

	return (
		<>
			<h2>{ __( 'You are using an old or cached session', locale ) }</h2>

			<p>{ __( 'This is usually fixed by doing one of the following:', locale ) }</p>
			<ul>
				<li>{ __( 'Reload the page - your current session is old.', locale ) }</li>
				<li>
					{ __(
						'Log out, clear your browser cache, and log in again - your browser has cached an old session.', locale
					) }
				</li>
				<li>
					{ __(
						'Your admin pages are being cached. Clear this cache and try again. There may be multiple caches involved.', locale
					) }
				</li>
			</ul>

			<ErrorDebug { ...props } mini />
		</>
	);
}

export default DisplayNonceError;
