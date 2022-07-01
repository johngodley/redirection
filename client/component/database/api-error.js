/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';

export default function DatabaseApiError( { onRetry } ) {
	return (
		<div className="redirection-database_error wpl-error">
			<h3>{ __( 'Database problem' ) }</h3>

			<p>
				<button className="button button-primary" onClick={ onRetry }>
					{ __( 'Try again' ) }
				</button>
			</p>
		</div>
	);
}
