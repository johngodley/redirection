import { __ } from '@wordpress/i18n';
import 'component/import-export/style.scss';
import ExportActionSelector from './export-action-selector';
import ExportOptions from './export-options';
import ExportResults from './export-results';
import ExportSummaryCard from './export-summary-card';
import ExportTypeSelector from './export-type-selector';
import useExportPage from './use-export-page';

function ExportPage() {
	const { exportTypes, availableFormats, groupRows, summaryMeta, state, onChange, onDownload, onSelectType, onView } =
		useExportPage();

	return (
		<div className="redirect-io-page">
			<section className="io-section io-section--export">
				<p>
					{ __(
						'Export redirects, redirect logs, or 404 logs. Redirection JSON contains complete redirect data, while other formats contain the information appropriate to that format.',
						'redirection'
					) }
				</p>

				<ExportTypeSelector
					exportTypes={ exportTypes }
					activeType={ state.exportType }
					isExporting={ state.isExporting }
					onSelect={ onSelectType }
				/>

				<ExportOptions
					exportType={ state.exportType }
					redirectScopeType={ state.redirectScopeType }
					redirectModule={ state.redirectModule }
					redirectGroup={ state.redirectGroup }
					groupRows={ groupRows }
					format={ state.format }
					availableFormats={ availableFormats }
					disabled={ state.isExporting }
					onChange={ onChange }
				/>

				<div className="export-summary">
					<ExportSummaryCard
						meta={ summaryMeta }
						isPreviewLoading={ state.isPreviewLoading }
						previewTotal={ state.previewTotal }
						previewEstimatedSize={ state.previewEstimatedSize }
					/>
				</div>

				<ExportActionSelector disabled={ state.isExporting } onView={ onView } onDownload={ onDownload } />

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
