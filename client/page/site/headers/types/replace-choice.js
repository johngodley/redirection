/**
 * External dependencies
 */

import React from 'react';

/**
 * Internal dependencies
 */
import Select from 'component/select';

const getReplace = name => '<' + name + '>';
const hasReplace = ( value, replace ) => value.indexOf( getReplace( replace ) ) !== -1;

function validateValue( value, replaceType ) {
	if ( replaceType === 'uri' ) {
		return value.replace( /[^A-Za-z0-9-._~:/?#\[\]@!$&'()*+,;=]/g, '' );
	} else if ( replaceType === 'integer' ) {
		return parseInt( value, 10 );
	}

	return value;
}

const HeaderReplaceSimpleChoice = ( { headerValue, headerSettings, options, onChange } ) => {
	const { choices, replace, replaceType } = options;
	const selected = headerSettings.selected ? headerSettings.selected : headerValue;
	const replaceValue = headerSettings.replaceValue ? headerSettings.replaceValue : '';
	const onChangeReplace = ev => {
		onChange( {
			headerValue: selected.replace( getReplace( replace ), ev.target.value ),
			headerSettings: {
				selected,
				replaceValue: validateValue( ev.target.value, replaceType ),
			},
		} );
	};
	const onChangeHeader = ev => {
		onChange( {
			headerValue: ev.target.value,
			headerSettings: {
				selected: ev.target.value,
				replaceValue: '',
			},
		} );
	};

	return (
		<React.Fragment>
			<Select items={ choices } name="headerValue" value={ selected } onChange={ onChangeHeader } />

			{ hasReplace( selected, replace ) && (
				<label>
					{ getReplace( replace ).replace( '<', '' ).replace( '>', '' ) }

					<input type="text" className="regular-text" name="replaceValue" value={ replaceValue } onChange={ onChangeReplace } />
				</label>
			) }
		</React.Fragment>
	);
};

export default HeaderReplaceSimpleChoice;
