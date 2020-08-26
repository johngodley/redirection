/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';

const Importer = props => {
	const { plugin, doImport } = props;
	const { name, total } = plugin;
	const clicker = () => {
		doImport( plugin );
	};

	return (
		<div className="plugin-importer">
			<p><strong>{ name }</strong> ({ __( 'total = ' ) + total } )</p>

			<button onClick={ clicker } className="button-secondary">
				{ __( 'Import from %s', { args: name } ) }
			</button>
		</div>
	);
};

export default Importer;
