/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';

const ApiResultPass = ( methods ) => {
	return (
		<p key={ methods }>
			<span className="dashicons dashicons-yes"></span>

			{ methods.map( ( method, key ) => <span key={ key } className="api-result-method_pass">{ method }</span> ) }

			{ __( 'Working!', 'redirection' ) }
		</p>
	);
};

export default ApiResultPass;
