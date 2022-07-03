/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';

export default function DatabaseApiError( { onRetry } ) {
	return (
		<div className="redirection-database_error wpl-error">
			<h3>{ __( 'Database problem', 'redirection' ) }</h3>

			<p>
				<button className="button button-primary" onClick={ onRetry }>
					{ __( 'Try again', 'redirection' ) }
				</button>
			</p>
		</div>
	);
}
