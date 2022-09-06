/**
 * External dependencies
 */

import React from 'react';

/**
 * Internal dependencies
 */

import { getSourceFlags } from '../../../component/redirect-edit/constants';
import RedirectFlag from './source-flag';

function SourceFlags( props ) {
	const { row, defaultFlags } = props;
	const {
		match_data: { source },
	} = row;

	return Object.keys( source )
		.filter( ( key ) => defaultFlags[ key ] !== source[ key ] && key !== 'flag_query' )
		.map( ( key ) => {
			const displayName = getSourceFlags().find( ( item ) => item.value === key );

			return <RedirectFlag key={ key } name={ displayName.label } className={ 'redirect-source__' + key } />;
		} );
}

export default SourceFlags;
