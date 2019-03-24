/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';

const ApiResultPass = ( methods ) => {
	return (
		<p key={ methods }>
			<span className="dashicons dashicons-yes"></span>

			{ methods.map( ( method, key ) => <span key={ key } className="api-result-method_pass">{ method }</span> ) }

			{ __( 'Working!' ) }
		</p>
	);
};

export default ApiResultPass;
