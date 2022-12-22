/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';

const HeaderPlainValue = ( { headerValue, onChange } ) => {
	return (
		<label>{ __( 'Value', 'redirection' ) }: <input type="text" className="regular-text" name="headerValue" value={ headerValue } onChange={ ev => onChange( { [ ev.target.name ]: ev.target.value } ) } /></label>
	);
};

export default HeaderPlainValue;
