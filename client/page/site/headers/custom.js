/**
 * External dependencies
 */

import React from 'react';

const HeaderCustom = ( { headerValue, headerName, onChange } ) => {
	return (
		<React.Fragment>
			<input type="text" name="headerName" value={ headerName } onChange={ ev => onChange( { [ ev.target.name ]: ev.target.value } ) } />
			Value: <input type="text" name="headerValue" value={ headerValue } onChange={ ev => onChange( { [ ev.target.name ]: ev.target.value } ) } />
		</React.Fragment>
	);
};

export default HeaderCustom;
