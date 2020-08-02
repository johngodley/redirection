/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'wp-plugin-lib/locale';

/**
 * Internal dependencies
 */

import RedirectFlag from './source-flag';

function SourceQuery( props ) {
	const { defaultFlags, row } = props;
	const {
		match_data: { source },
	} = row;

	if ( defaultFlags.flag_query !== source.flag_query ) {
		let name = __( 'Exact Query' );

		if ( source.flag_query === 'ignore' ) {
			name = __( 'Ignore Query' );
		} else if ( source.flag_query === 'pass' ) {
			name = __( 'Ignore & Pass Query' );
		}

		return <RedirectFlag name={ name } />;
	}

	return null;
}

export default SourceQuery;
