/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';

const HeaderPlainValue = ( { headerValue, onChange } ) => {
	return (
		<label>{ __( 'Value' ) }: <input type="text" className="regular-text" name="headerValue" value={ headerValue } onChange={ ev => onChange( { [ ev.target.name ]: ev.target.value } ) } /></label>
	);
};

export default HeaderPlainValue;
