/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */
import { getSourceFlags } from './constants';
import TableRow from './table-row';
import MultiOptionDropdown from 'component/multi-option-dropdown';

const getUrlFlags = ( { flag_case, flag_regex, flag_trailing } ) => ( {
	flag_case,
	flag_regex,
	flag_trailing,
} );

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
		<TableRow title={ __( 'Source URL' ) } className="redirect-edit__source">
			<input
				type="text"
				name="url"
				value={ url }
				onChange={ onChange }
				autoFocus={ autoFocus }
				className="regular-text"
				placeholder={ __( 'The relative URL you want to redirect from' ) }
			/>

			<MultiOptionDropdown
				options={ flagOptions }
				selected={ getUrlFlags( flags ) }
				onApply={ onFlagChange }
				title={ __( 'URL options / Regex' ) }
				badges
				hideTitle
			/>
		</TableRow>
	);
};

export default RedirectSourceUrl;
