/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

import TableRow from '../table-row';
import TargetUrl from '../target';

const ActionUrl = ( { onChange, data } ) => {
	const { url } = data;

	return (
		<TableRow title={ __( 'Target URL', 'redirection' ) } className="redirect-edit__target">
			<TargetUrl
				url={ url }
				onChange={ ( value ) => onChange( { target: { name: 'url', value, type: 'input' } } ) }
			/>
		</TableRow>
	);
};

ActionUrl.propTypes = {
	data: PropTypes.object.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default ActionUrl;
