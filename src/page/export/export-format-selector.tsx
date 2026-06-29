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
		<div className="groups__row">
			<div className="groups__label">{ __( 'Format', 'redirection' ) }</div>
			<div className="groups__control">
				<select value={ format } disabled={ disabled } onChange={ ( event ) => onChange( event.target.value ) }>
					{ availableFormats.map( ( item ) => (
						<option key={ item } value={ item }>
							{ getExportFormatOptionLabel( item ) }
						</option>
					) ) }
				</select>
			</div>
		</div>
	);
}

export default ExportFormatSelector;
