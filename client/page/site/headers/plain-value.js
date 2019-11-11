/**
 * External dependencies
 */

import React from 'react';

const HeaderPlainValue = ( { headerValue, onChange } ) => {
	return (
		<React.Fragment>
			Value: <input type="text" name="headerValue" value={ headerValue } onChange={ ev => onChange( { [ ev.target.name ]: ev.target.value } ) } />
		</React.Fragment>
	);
};

export default HeaderPlainValue;
