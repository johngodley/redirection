/**
 * External dependencies
 */

import { __, sprintf } from '@wordpress/i18n';

function Importer( props ) {
	const { plugin, doImport } = props;
	const { name, total } = plugin;
	const clicker = () => {
		doImport( plugin );
	};

	return (
		<div className="plugin-importer">
			<p><strong>{ name }</strong> ({ sprintf( __( 'total = %d', 'redirection' ), total ) })</p>

			<button onClick={ clicker } className="button-secondary">
				{ sprintf( __( 'Import from %s', 'redirection' ), name ) }
			</button>
		</div>
	);
};

export default Importer;
