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

const ActionUrlFrom = ( { onChange, data } ) => {
	const { url_from, url_notfrom } = data;

	return (
		<>
			<TableRow title={ __( 'Matched Target' ) } className="redirect-edit__target__matched">
				<input type="text" className="regular-text" name="url_from" value={ url_from } onChange={ onChange } placeholder={ __( 'Target URL when matched (empty to ignore)' ) } />
			</TableRow>
			<TableRow title={ __( 'Unmatched Target' ) } className="redirect-edit__target__unmatched">
				<input type="text" className="regular-text" name="url_notfrom" value={ url_notfrom } onChange={ onChange } placeholder={ __( 'Target URL when not matched (empty to ignore)' ) } />
			</TableRow>
		</>
	);
};

ActionUrlFrom.propTypes = {
	data: PropTypes.object.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default ActionUrlFrom;
