import { __ } from '@wordpress/i18n';
import 'component/import-export/style.scss';
import ImportExportApiError from 'component/import-export/api-error';
import ExportActionSelector from './export-action-selector';
import ExportOptions from './export-options';
import ExportResults from './export-results';
import ExportSummaryCard from './export-summary-card';
import ExportTypeSelector from './export-type-selector';
import useExportPage from './use-export-page';

function ExportPage() {
	const {
		exportTypes,
		availableFormats,
		groupRows,
		hasAllTypesSelected,
		hasSelectedTypes,
		summaryMeta,
		state,
		onChange,
		onDownload,
		onToggleAllTypes,
		onToggleType,
		onView,
	} = useExportPage();

	return (
		<div className="redirect-io-page">
			<section className="io-section io-section--export">
				<p>
					{ __(
						'Export redirects, redirect logs, 404 logs, groups, or settings. You can combine multiple export cards, and mixed exports are bundled as JSON.',
						'redirection'
					) }
				</p>

				<div className="inline-notice notice-warning">
					<p>
						{ __(
							'You can also export directly from the redirects, redirect logs, or 404 logs pages for more control over filtering.',
							'redirection'
						) }
					</p>
				</div>

				<div className="export-types-toggle">
					<label className="groups__checkbox" htmlFor="export-types-toggle-all">
						<input
							id="export-types-toggle-all"
							type="checkbox"
							checked={ hasAllTypesSelected }
							onChange={ ( event ) => onToggleAllTypes( event.target.checked ) }
							disabled={ state.isExporting }
						/>
						<span>{ __( 'Select all', 'redirection' ) }</span>
					</label>
				</div>

				<ExportTypeSelector
					exportTypes={ exportTypes }
					selectedTypes={ state.selectedTypes }
					redirectScopeType={ state.redirectScopeType }
					redirectModule={ state.redirectModule }
					redirectGroup={ state.redirectGroup }
					groupRows={ groupRows }
					isExporting={ state.isExporting }
					onChange={ onChange }
					onToggle={ onToggleType }
				/>

				<ExportOptions
					selectedTypes={ state.selectedTypes }
					format={ state.format }
					availableFormats={ availableFormats }
					disabled={ state.isExporting || ! hasSelectedTypes }
					onChange={ ( name, value ) => onChange( name, value ) }
				/>

				{ state.currentError && <ImportExportApiError error={ state.currentError } /> }

				<div className="export-summary">
					<ExportSummaryCard
						meta={ summaryMeta }
						isPreviewLoading={ state.isPreviewLoading }
						previewTotal={ state.previewTotal }
						previewEstimatedSize={ state.previewEstimatedSize }
					/>
				</div>

				<ExportActionSelector
					disabled={ state.isExporting || ! hasSelectedTypes }
					onView={ onView }
					onDownload={ onDownload }
				/>

				{ state.isExporting && (
					<div className="loader-wrapper loader-textarea">
						<div className="wpl-placeholder__loading"></div>
					</div>
				) }

				{ ! state.isExporting && <ExportResults lastResult={ state.lastResult } /> }
			</section>
		</div>
	);
}

export default ExportPage;
