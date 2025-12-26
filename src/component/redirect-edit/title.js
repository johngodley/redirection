/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';

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
		<TableRow title={ __( 'Title', 'redirection' ) } className="redirect-edit__title">
			<input
				type="text"
				name="title"
				value={ title }
				onChange={ ( ev ) => onChange( { title: ev.target.value } ) }
				placeholder={ __( 'Describe the purpose of this redirect (optional)', 'redirection' ) }
			/>
		</TableRow>
	);
}

export default RedirectTitle;
