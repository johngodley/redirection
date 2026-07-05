import { __ } from '@wordpress/i18n';
import { createInterpolateElement, Placeholder } from '@wp-plugin-components';
import clsx from 'clsx';
import ImportExportApiError from 'component/import-export/api-error';
import IoCard from 'component/import-export/card';
import Importer from 'component/import-export/importer';
import { isJsonFile } from 'component/import-export/import-sniff';
import 'component/import-export/style.scss';
import FileDropzone from './file-dropzone';
import ImportOptions from './import-options';
import ImportResults from './import-results';
import PasteImporter from './paste-importer';
import type { ImportPlugin } from './types';
import useImportPage from './use-import-page';

function ImportPage() {
	const {
		state,
		fileInputRef,
		importingStatus,
		onAddFileClick,
		onCancel,
		onClearFile,
		onClearPaste,
		onDragEnter,
		onDragLeave,
		onDragOver,
		onDrop,
		onFileInputChange,
		onImport,
		onOptionsChange,
		onSelectFileImporter,
		onSelectPasteImporter,
		onPasteTextChange,
		onSelectPlugin,
	} = useImportPage();

	const activeFile = state.activeImportType === 'paste' ? state.pasteFile : state.file;
	const activeFileInfo = state.activeImportType === 'paste' ? state.pasteInfo : state.fileInfo;

	const renderImporters = ( importerList: ImportPlugin[] ) => {
		return (
			<>
				{ importerList.map( ( item ) => (
					<Importer
						plugin={ item }
						key={ item.id }
						onSelect={ onSelectPlugin }
						isActive={ state.activePluginId === item.id && state.activeImportType === 'plugin' }
						isImporting={ state.isImporting }
					/>
				) ) }
			</>
		);
	};
	const hasNoSelectedJsonSections =
		( state.activeImportType === 'file' || state.activeImportType === 'paste' ) &&
		activeFileInfo?.format === 'json' &&
		activeFileInfo.valid &&
		state.selectedSections.length === 0;
	const hasUnsupportedCsvImport =
		( state.activeImportType === 'file' || state.activeImportType === 'paste' ) &&
		activeFileInfo?.format === 'csv' &&
		activeFileInfo.valid &&
		activeFileInfo.importSupported === false;
	const jsonSections =
		activeFileInfo?.format === 'json' && activeFileInfo.valid && activeFileInfo.contents
			? activeFileInfo.contents
			: null;
	const requiresGroups =
		state.activeImportType === 'plugin' ||
		( ( state.activeImportType === 'file' || state.activeImportType === 'paste' ) &&
			( ( jsonSections !== null &&
				Number( jsonSections.redirects || 0 ) > 0 &&
				state.selectedSections.includes( 'redirects' ) ) ||
				( activeFileInfo?.format === 'csv' &&
					activeFileInfo.valid &&
					activeFileInfo.importSupported === true ) ||
				( activeFileInfo?.format === 'apache' && activeFileInfo.valid ) ) );

	const renderImporterPlaceholder = () => {
		return (
			<IoCard title="" className="import-source-card import-source-card--placeholder">
				<Placeholder />
			</IoCard>
		);
	};

	return (
		<div
			className={ clsx( 'redirect-io-page', {
				'redirect-io-page--dragging': state.hover,
			} ) }
			onDragEnter={ onDragEnter }
			onDragLeave={ onDragLeave }
			onDragOver={ onDragOver }
			onDrop={ onDrop }
		>
			<section className="io-section io-section--import">
				<p>
					{ __(
						'Import redirects from a file or another plugin. You can also drag and drop a file anywhere on this page.',
						'redirection'
					) }
				</p>

				<div className="inline-notice notice-warning">
					<p>
						{ createInterpolateElement(
							__(
								'{{strong}}CSV file format{{/strong}}: {{code}}source URL, target URL{{/code}} - and can be optionally followed with {{code}}regex, http code{{/code}} ({{code}}regex{{/code}} - 0 for no, 1 for yes).',
								'redirection'
							),
							{
								code: <code />,
								strong: <strong />,
							}
						) }
					</p>
					<p>
						{ __(
							'CSV files need a recognised header otherwise they are assumed to be redirects. Use JSON for full data support.',
							'redirection'
						) }
					</p>
				</div>

				<div className="import-sources">
					<FileDropzone
						activeImportType={ state.activeImportType }
						file={ state.file }
						fileInfo={ state.fileInfo }
						fileInputRef={ fileInputRef }
						hover={ state.hover }
						importingStatus={ importingStatus }
						isImporting={ state.isImporting }
						isSniffing={ state.isSniffing }
						onAddFileClick={ onAddFileClick }
						onClearFile={ onClearFile }
						onClick={ onSelectFileImporter }
						onFileInputChange={ onFileInputChange }
					/>
					<PasteImporter
						activeImportType={ state.activeImportType }
						isImporting={ state.isImporting }
						pasteInfo={ state.pasteInfo }
						pasteText={ state.pasteText }
						onClearPaste={ onClearPaste }
						onPasteTextChange={ onPasteTextChange }
						onSelect={ onSelectPasteImporter }
					/>
					{ state.isLoadingImporters && renderImporterPlaceholder() }
					{ state.importers.length > 0 && renderImporters( state.importers as ImportPlugin[] ) }
				</div>

				{ ! state.hasCompletedImport && (
					<ImportOptions
						activeImportType={ state.activeImportType }
						activePluginId={ state.activePluginId }
						file={ activeFile }
						disabled={
							! state.hasActiveImport || state.isImporting || ( requiresGroups && ! state.hasGroups )
						}
						deleteSource={ state.deleteSource }
						duplicateMode={ state.duplicateMode }
						group={ state.group }
						groupRows={ state.groupRows }
						fileInfo={ activeFileInfo }
						isJsonFile={ isJsonFile }
						selectedSections={ state.selectedSections }
						onChange={ onOptionsChange }
					/>
				) }

				<div className="import-actions">
					{ state.hasCompletedImport ? (
						<button className="button-secondary" onClick={ onCancel }>
							{ __( 'OK', 'redirection' ) }
						</button>
					) : (
						<>
							<button
								className="button-secondary"
								onClick={ () => onImport( true ) }
								disabled={
									! state.hasActiveImport ||
									( requiresGroups && ! state.hasGroups ) ||
									! state.previewSupported ||
									hasNoSelectedJsonSections ||
									hasUnsupportedCsvImport ||
									state.isImporting ||
									( ( state.activeImportType === 'file' || state.activeImportType === 'paste' ) &&
										( activeFile === false ||
											state.isSniffing ||
											activeFileInfo === null ||
											activeFileInfo.valid === false ) )
								}
							>
								{ __( 'Preview redirects', 'redirection' ) }
							</button>{ ' ' }
							<button
								className="button-primary"
								onClick={ () => onImport( false ) }
								disabled={
									! state.hasActiveImport ||
									( requiresGroups && ! state.hasGroups ) ||
									hasNoSelectedJsonSections ||
									hasUnsupportedCsvImport ||
									state.isImporting ||
									( ( state.activeImportType === 'file' || state.activeImportType === 'paste' ) &&
										( activeFile === false ||
											state.isSniffing ||
											activeFileInfo === null ||
											activeFileInfo.valid === false ) )
								}
							>
								{ __( 'Import redirects', 'redirection' ) }
							</button>
						</>
					) }
				</div>

				{ state.currentError && <ImportExportApiError error={ state.currentError } /> }

				<ImportResults
					activeImportType={ state.activeImportType }
					lastImport={ state.lastImport }
					lastImportWasDryRun={ state.lastImportWasDryRun }
				/>
			</section>
		</div>
	);
}

export default ImportPage;
