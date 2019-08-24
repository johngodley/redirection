/**
 * External dependencies
 */

import React from 'react';

/**
 * Internal dependencies
 */
import { isEnabled } from './utils';

const Column = ( { enabled = true, className = null, children, selected } ) => {
	if ( isEnabled( selected, enabled ) ) {
		return (
			<td className={ className }>{ children }</td>
		);
	}

	return null;
};

export default Column;
