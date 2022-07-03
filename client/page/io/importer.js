/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';

const Importer = props => {
	const { plugin, doImport } = props;
	const { name, total } = plugin;
	const clicker = () => {
		doImport( plugin );
	};

	return (
		<div className="plugin-importer">
			<p><strong>{ name }</strong> ({ __( 'total = ', 'redirection' ) + total } )</p>

			<button onClick={ clicker } className="button-secondary">
				{ __( 'Import from %s', { args: name } ) }
			</button>
		</div>
	);
};

export default Importer;
