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

const MatchIp = ( { data, onChange } ) => {
	const { ip } = data;
	const changer = ev => {
		onChange( { target: { name: ev.target.name, value: ev.target.value.split( '\n' ) } } );
	};

	return (
		<TableRow title={ __( 'IP', 'redirection' ) } className="redirect-edit__match">
			<textarea value={ ip.join( '\n' ) } name="ip" placeholder={ __( 'Enter IP addresses (one per line)', 'redirection' ) } onChange={ changer } />
		</TableRow>
	);
};

MatchIp.propTypes = {
	data: PropTypes.object.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default MatchIp;
