/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'wp-plugin-lib/locale';

/**
 * Internal dependencies
 */

import ErrorDebug from '../debug';

function DisplayNonceError( props ) {
	return (
		<>
			<h2>{ __( 'You are using an old or cached session' ) }</h2>

			<p>{ __( 'This is usually fixed by doing one of the following:' ) }</p>
			<ul>
				<li>{ __( 'Reload the page - your current session is old.' ) }</li>
				<li>
					{ __(
						'Log out, clear your browser cache, and log in again - your browser has cached an old session.'
					) }
				</li>
				<li>
					{ __(
						'Your admin pages are being cached. Clear this cache and try again. There may be multiple caches involved.'
					) }
				</li>
			</ul>

			<ErrorDebug { ...props } mini />
		</>
	);
}

export default DisplayNonceError;
