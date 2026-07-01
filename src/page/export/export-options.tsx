import { __ } from '@wordpress/i18n';
import ExportFormatSelector from './export-format-selector';
import type { ExportFormat, ExportType } from './types';

interface ExportOptionsProps {
	selectedTypes: ExportType[];
	format: ExportFormat;
	availableFormats: ExportFormat[];
	disabled: boolean;
	onChange: ( name: 'format', value: string ) => void;
}

function ExportOptions( {
	selectedTypes,
	format,
	availableFormats,
	disabled,
	onChange,
}: ExportOptionsProps ) {
	const hasSelectedTypes = selectedTypes.length > 0;

	return (
		<div className="groups export-options">
			<h3>{ __( 'Export options', 'redirection' ) }</h3>

			{ hasSelectedTypes && (
				<ExportFormatSelector
					format={ format }
					availableFormats={ availableFormats }
					disabled={ disabled }
					onChange={ ( value ) => onChange( 'format', value ) }
				/>
			) }

			{ ! hasSelectedTypes && (
				<div className="export-options__field">
					<span className="export-options__label">{ __( 'Format', 'redirection' ) }</span>
					{ __( 'Select one or more export types to choose a format.', 'redirection' ) }
				</div>
			) }
		</div>
	);
}

export default ExportOptions;
