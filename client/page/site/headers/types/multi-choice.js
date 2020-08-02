/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'wp-plugin-lib/locale';

/**
 * Internal dependencies
 */
import MultiOptionDropdown from 'wp-plugin-components/multi-option-dropdown';

const arrayToObject = array => {
	const obj = {};

	array.map( item => obj[ item ] = true );

	return obj;
};

const HeaderMultiChoice = ( { headerValue, options, onChange } ) => {
	const { choices, implode, wildCard } = options;
	const selected = headerValue.split( implode );
	const applyItem = ( items, added ) => {
		if ( added === wildCard ) {
			onChange( { headerValue: selected.length === 1 && selected[ 0 ] === wildCard ? '' : wildCard } );
		} else {
			onChange( { headerValue: Object.keys( items ).filter( item => item !== wildCard ).filter( item => item ).join( implode ) } );
		}
	};

	return (
		<MultiOptionDropdown
			options={ wildCard ? choices.concat( [ { value: wildCard, label: __( 'All' ) } ] ) : choices }
			selected={ arrayToObject( selected ) }
			onApply={ applyItem }
			title={ __( 'Values' ) }
			hideTitle
			badges
		/>
	);
};

export default HeaderMultiChoice;
