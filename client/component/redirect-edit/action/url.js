/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */

import TableRow from '../table-row';
import TargetUrl from '../target';

const ActionUrl = ( { onChange, data } ) => {
	const { url } = data;

	return (
		<TableRow title={ __( 'Target URL' ) }>
			<TargetUrl url={ url } onChange={ onChange } />
		</TableRow>
	);
};

ActionUrl.propTypes = {
	data: PropTypes.object.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default ActionUrl;
