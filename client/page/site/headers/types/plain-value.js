/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';

const HeaderPlainValue = ( { headerValue, onChange } ) => {
	return (
		<React.Fragment>
			<label>{ __( 'Value' ) }: <input type="text" className="regular-text" name="headerValue" value={ headerValue } onChange={ ev => onChange( { [ ev.target.name ]: ev.target.value } ) } /></label>
		</React.Fragment>
	);
};

export default HeaderPlainValue;
