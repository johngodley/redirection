import { __, _n } from '@wordpress/i18n';
import clsx from 'clsx';
import IoCard from 'component/import-export/card';
import type { ExportType, ExportTypeOption } from './types';

interface ExportTypeSelectorProps {
	exportTypes: ExportTypeOption[];
	activeType: ExportType;
	isExporting: boolean;
	onSelect: ( exportType: ExportType ) => void;
}

function ExportTypeSelector( { exportTypes, activeType, isExporting, onSelect }: ExportTypeSelectorProps ) {
	return (
		<div className="export-types">
			{ exportTypes.map( ( exportType ) => (
				<IoCard
					key={ exportType.id }
					title={ exportType.name }
					badge={ __( 'Export', 'redirection' ) }
					meta={ [
						{
							label: __( 'Export type', 'redirection' ),
							value: exportType.description,
							fullWidth: true,
						},
					] }
					stats={ [
						{
							label: _n( 'Format', 'Formats', exportType.formats.length, 'redirection' ),
							value: exportType.formats.length,
						},
					] }
					actions={
						<button
							type="button"
							onClick={ () => onSelect( exportType.id ) }
							className="button-secondary"
							disabled={ isExporting || activeType === exportType.id }
						>
							{ activeType === exportType.id
								? __( 'Selected', 'redirection' )
								: __( 'Use export', 'redirection' ) }
						</button>
					}
					className={ clsx( 'import-source-card', {
						'import-source-card--active': activeType === exportType.id,
					} ) }
				/>
			) ) }
		</div>
	);
}

export default ExportTypeSelector;
