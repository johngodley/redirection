/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';

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
		let name = __( 'Exact Query', 'redirection' );

		if ( source.flag_query === 'ignore' ) {
			name = __( 'Ignore Query', 'redirection' );
		} else if ( source.flag_query === 'pass' ) {
			name = __( 'Ignore & Pass Query', 'redirection' );
		}

		return <RedirectFlag name={ name } />;
	}

	return null;
}

export default SourceQuery;
