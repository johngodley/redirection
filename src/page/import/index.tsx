import { __ } from '@wordpress/i18n';
import { createInterpolateElement, Placeholder } from '@wp-plugin-components';
import clsx from 'clsx';
import IoCard from 'component/import-export/card';
import Importer from 'component/import-export/importer';
import { isJsonFile } from 'component/import-export/import-sniff';
import 'component/import-export/style.scss';
import FileDropzone from './file-dropzone';
import ImportOptions from './import-options';
import ImportResults from './import-results';
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
		onDragEnter,
		onDragLeave,
		onDragOver,
		onDrop,
		onFileInputChange,
		onImport,
		onOptionsChange,
		onSelectFileImporter,
		onSelectPlugin,
	} = useImportPage();

	const renderImporters = ( importerList: ImportPlugin[] ) => {
		return (
			<>
				{ importerList.map( ( item, pos ) => (
					<Importer
						plugin={ item }
						key={ pos }
						onSelect={ onSelectPlugin }
						isActive={ state.activePluginId === item.id && state.activeImportType === 'plugin' }
						isImporting={ state.isImporting }
					/>
				) ) }
			</>
		);
	};

	const renderImporterPlaceholder = () => {
		return (
			<IoCard className="import-source-card import-source-card--placeholder">
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
							'CSV does not include all information, and everything is imported/exported as "URL only" matches. Use the JSON format for a full set of data.',
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
						isJsonFile={ isJsonFile }
						isSniffing={ state.isSniffing }
						onAddFileClick={ onAddFileClick }
						onClearFile={ onClearFile }
						onClick={ onSelectFileImporter }
						onFileInputChange={ onFileInputChange }
					/>
					{ state.isLoadingImporters && renderImporterPlaceholder() }
					{ state.importers.length > 0 && renderImporters( state.importers as ImportPlugin[] ) }
				</div>

				{ ! state.hasCompletedImport && (
					<ImportOptions
						activeImportType={ state.activeImportType }
						activePluginId={ state.activePluginId }
						file={ state.file }
						disabled={ ! state.hasActiveImport || state.isImporting }
						deleteSource={ state.deleteSource }
						duplicateMode={ state.duplicateMode }
						group={ state.group }
						groupRows={ state.groupRows }
						isJsonFile={ isJsonFile }
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
									! state.previewSupported ||
									state.isImporting ||
									( state.activeImportType === 'file' &&
										( state.file === false ||
											state.isSniffing ||
											state.fileInfo === null ||
											state.fileInfo.valid === false ) )
								}
							>
								{ __( 'Preview redirects', 'redirection' ) }
							</button>{ ' ' }
							<button
								className="button-primary"
								onClick={ () => onImport( false ) }
								disabled={
									! state.hasActiveImport ||
									state.isImporting ||
									( state.activeImportType === 'file' &&
										( state.file === false ||
											state.isSniffing ||
											state.fileInfo === null ||
											state.fileInfo.valid === false ) )
								}
							>
								{ __( 'Import redirects', 'redirection' ) }
							</button>
						</>
					) }
				</div>

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
