import { __ } from '@wordpress/i18n';
import { getExportFormatOptionLabel } from './export-helpers';
import type { ExportFormat } from './types';

interface ExportFormatSelectorProps {
	format: ExportFormat;
	availableFormats: ExportFormat[];
	disabled: boolean;
	onChange: ( value: string ) => void;
}

function ExportFormatSelector( { format, availableFormats, disabled, onChange }: ExportFormatSelectorProps ) {
	return (
		<div className="export-options__field">
			<label className="export-options__label" htmlFor="export-format">
				{ __( 'Format', 'redirection' ) }
			</label>
			<select
				id="export-format"
				value={ format }
				disabled={ disabled }
				onChange={ ( event ) => onChange( event.target.value ) }
			>
				{ availableFormats.map( ( item ) => (
					<option key={ item } value={ item }>
						{ getExportFormatOptionLabel( item ) }
					</option>
				) ) }
			</select>
		</div>
	);
}

export default ExportFormatSelector;
