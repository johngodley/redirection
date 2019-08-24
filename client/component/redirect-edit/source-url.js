/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { getSourceFlags } from './constants';
import TableRow from './table-row';
import MultiOptionDropdown from 'component/multi-option-dropdown';

function getFlagDifference( flags, defaultFlags ) {
	const diff = {};

	Object.keys( defaultFlags ).map( key => {
		if ( flags[ key ] !== defaultFlags[ key ] ) {
			diff[ key ] = flags[ key ];
		}
	} );

	return diff;
}

const RedirectSourceUrl = ( { url, flags, defaultFlags, onFlagChange, onChange, autoFocus = false } ) => {
	const flagOptions = getSourceFlags();

	if ( Array.isArray( url ) ) {
		return (
			<TableRow title={ __( 'Source URL' ) } className="top">
				<textarea value={ url.join( '\n' ) } readOnly></textarea>
			</TableRow>
		);
	}

	return (
		<TableRow title={ __( 'Source URL' ) }>
			<input
				type="text"
				name="url"
				value={ url }
				onChange={ onChange }
				autoFocus={ autoFocus }
				placeholder={ __( 'The relative URL you want to redirect from' ) }
			/>

			<MultiOptionDropdown
				options={ flagOptions }
				selected={ getFlagDifference( flags, defaultFlags ) }
				onApply={ onFlagChange }
				title={ __( 'URL options / Regex' ) }
				badges
				hideTitle
			/>
		</TableRow>
	);
};

RedirectSourceUrl.propTypes = {
	url: PropTypes.string.isRequired,
	flags: PropTypes.object.isRequired,
	onFlagChange: PropTypes.func.isRequired,
	onChange: PropTypes.func.isRequired,
	autoFocus: PropTypes.bool,
	defaultFlags: PropTypes.object.isRequired,
};

export default RedirectSourceUrl;
