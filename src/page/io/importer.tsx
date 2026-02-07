import { __, sprintf } from '@wordpress/i18n';

interface Plugin {
	name: string;
	total: number;
}

interface ImporterProps {
	plugin: Plugin;
	doImport: ( plugin: Plugin ) => void;
}

function Importer( props: ImporterProps ) {
	const { plugin, doImport } = props;
	const { name, total } = plugin;
	const clicker = () => {
		doImport( plugin );
	};

	return (
		<div className="plugin-importer">
			<p>
				<strong>{ name }</strong> (
				{
					/* translators: %d is the number of redirects that can be imported */
					sprintf( __( 'total = %d', 'redirection' ), total )
				}
				)
			</p>

			<button type="button" onClick={ clicker } className="button-secondary">
				{ sprintf(
					// translators: %s is the plugin name
					__( 'Import from %s', 'redirection' ),
					name
				) }
			</button>
		</div>
	);
}

export default Importer;
