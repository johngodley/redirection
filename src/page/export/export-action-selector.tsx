import { __ } from '@wordpress/i18n';

interface ExportActionSelectorProps {
	disabled: boolean;
	onView: () => void;
	onDownload: () => void;
}

function ExportActionSelector( { disabled, onView, onDownload }: ExportActionSelectorProps ) {
	return (
		<div className="import-actions">
			<button type="button" className="button-secondary" onClick={ onView } disabled={ disabled }>
				{ __( 'View', 'redirection' ) }
			</button>{ ' ' }
			<button type="button" className="button-primary" onClick={ onDownload } disabled={ disabled }>
				{ __( 'Download', 'redirection' ) }
			</button>
		</div>
	);
}

export default ExportActionSelector;
