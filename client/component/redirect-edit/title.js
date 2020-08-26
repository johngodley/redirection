/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

import TableRow from './table-row';

const RedirectTitle = ( { title, onChange } ) => {
	return (
		<TableRow title={ __( 'Title' ) } className="redirect-edit__title">
			<input
				type="text"
				name="title"
				value={ title }
				onChange={ onChange }
				placeholder={ __( 'Describe the purpose of this redirect (optional)' ) }
			/>
		</TableRow>
	);
};

RedirectTitle.propTypes = {
	title: PropTypes.string.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default RedirectTitle;
