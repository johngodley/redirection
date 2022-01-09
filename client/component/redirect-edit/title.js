/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';

/**
 * Internal dependencies
 */

import TableRow from './table-row';

/**
 * Source URL
 * @param {object} props Component props.
 * @param {import('.').Redirect} props.redirect URL.
 * @param {*} props.onChange
 * @returns {import('react').ReactElement}
 */
function RedirectTitle( { title, onChange } ) {
	return (
		<TableRow title={ __( 'Title' ) } className="redirect-edit__title">
			<input
				type="text"
				name="title"
				value={ title }
				onChange={ ( ev ) => onChange( { title: ev.target.value } ) }
				placeholder={ __( 'Describe the purpose of this redirect (optional)' ) }
			/>
		</TableRow>
	);
}

export default RedirectTitle;
