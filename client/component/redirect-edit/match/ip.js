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

const MatchIp = ( { data, onChange } ) => {
	const { ip } = data;
	const changer = ev => {
		onChange( { target: { name: ev.target.name, value: ev.target.value.split( '\n' ) } } );
	};

	return (
		<TableRow className="top" title={ __( 'IP' ) }>
			<textarea value={ ip.join( '\n' ) } name="ip" placeholder={ __( 'Enter IP addresses (one per line)' ) } onChange={ changer } />
		</TableRow>
	);
};

MatchIp.propTypes = {
	data: PropTypes.object.isRequired,
	onChange: PropTypes.func.isRequired,
};

export default MatchIp;
