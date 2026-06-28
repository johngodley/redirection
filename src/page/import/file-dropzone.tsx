import { __, _n } from '@wordpress/i18n';
import clsx from 'clsx';
import IoCard, { type CardMetaItem, type CardStatItem } from 'component/import-export/card';
import type { ImportSniffResult } from './types';

interface FileDropzoneProps {
	activeImportType: 'file' | 'plugin' | null;
	file: File | false;
	fileInfo: ImportSniffResult | null;
	fileInputRef: React.RefObject< HTMLInputElement >;
	hover: boolean;
	importingStatus: string;
	isImporting: boolean;
	isSniffing: boolean;
	onAddFileClick: () => void;
	onClearFile: () => void;
	onClick: () => void;
	onFileInputChange: ( event: React.ChangeEvent< HTMLInputElement > ) => void;
}

function FileDropzone( {
	activeImportType,
	file,
	fileInfo,
	fileInputRef,
	hover,
	importingStatus,
	isImporting,
	isSniffing,
	onAddFileClick,
	onClearFile,
	onClick,
	onFileInputChange,
}: FileDropzoneProps ) {
	const getFileErrorMessage = () => {
		if ( fileInfo?.error === 'not-redirection-json' ) {
			return __( 'Not a Redirection JSON export', 'redirection' );
		}

		if ( fileInfo?.error === 'invalid-json' ) {
			return __( 'Invalid JSON', 'redirection' );
		}

		if ( fileInfo?.error === 'empty-csv' ) {
			return __( 'Empty CSV file', 'redirection' );
		}

		if ( fileInfo?.error === 'separator-not-detected' ) {
			return __( 'Unable to detect a CSV separator', 'redirection' );
		}

		if ( fileInfo?.error === 'unsupported-file-type' ) {
			return __( 'Unsupported file type', 'redirection' );
		}

		if ( fileInfo?.error === 'read-failed' ) {
			return __( 'Unable to read file', 'redirection' );
		}

		return '';
	};

	const getSeparatorText = () => {
		if ( fileInfo?.format !== 'csv' || ! fileInfo.separator ) {
			return '';
		}

		if ( fileInfo.separator === ',' ) {
			return __( 'Comma', 'redirection' );
		}

		if ( fileInfo.separator === ';' ) {
			return __( 'Semicolon', 'redirection' );
		}

		if ( fileInfo.separator === '|' ) {
			return __( 'Pipe', 'redirection' );
		}

		return __( 'Tab', 'redirection' );
	};

	const getFileSize = () => {
		if ( file === false ) {
			return '';
		}

		if ( file.size < 1024 ) {
			return `${ file.size } B`;
		}

		if ( file.size < 1024 * 1024 ) {
			return `${ ( file.size / 1024 ).toFixed( 1 ) } KB`;
		}

		return `${ ( file.size / ( 1024 * 1024 ) ).toFixed( 1 ) } MB`;
	};

	const renderSelectedFileCard = () => {
		if ( file === false ) {
			return null;
		}

		if ( isSniffing ) {
			return <p>{ __( 'Inspecting file…', 'redirection' ) }</p>;
		}

		if ( fileInfo === null ) {
			return null;
		}

		if ( ! fileInfo.valid ) {
			return (
				<div className="inline-notice inline-error">
					<p>{ getFileErrorMessage() }</p>
				</div>
			);
		}

		const details: CardMetaItem[] = [
			{
				label: __( 'Details', 'redirection' ),
				value: getFileSize(),
			},
		];
		const stats: CardStatItem[] = [];
		let type = '';

		if ( fileInfo.format === 'json' ) {
			type = __( 'JSON', 'redirection' );
			details.push( {
				label: __( 'Plugin version', 'redirection' ),
				value: fileInfo.version || '',
			} );
			stats.push(
				{
					label: _n( 'Group', 'Groups', fileInfo.groups || 0, 'redirection' ),
					value: fileInfo.groups || 0,
				},
				{
					label: _n( 'Redirect', 'Redirects', fileInfo.redirects || 0, 'redirection' ),
					value: fileInfo.redirects || 0,
				}
			);
		}

		if ( fileInfo.format === 'csv' ) {
			type = __( 'CSV', 'redirection' );
			details.push( {
				label: __( 'Separator', 'redirection' ),
				value: getSeparatorText(),
			} );
			stats.push( {
				label: _n( 'Redirect', 'Redirects', fileInfo.rows || 0, 'redirection' ),
				value: fileInfo.rows || 0,
			} );
		}

		return (
			<IoCard
				title={ file.name }
				badge={ __( 'File', 'redirection' ) }
				meta={ [ { label: __( 'Import type', 'redirection' ), value: type }, ...details ] }
				stats={ stats }
				wrapped={ false }
			/>
		);
	};

	const renderInitialDrop = () => {
		return (
			<IoCard
				title={ __( 'Import file', 'redirection' ) }
				badge={ __( 'File', 'redirection' ) }
				meta={ [
					{ label: __( 'Import type', 'redirection' ), value: __( 'Upload a file', 'redirection' ) },
					{
						label: __( 'Supported files', 'redirection' ),
						value: __( 'CSV, JSON, and .htaccess', 'redirection' ),
					},
				] }
				children={
					<div className="file-sniff__meta-description">
						{ __( 'Drag and drop a file anywhere on this page.', 'redirection' ) }
					</div>
				}
				actions={
					<button type="button" className="button-secondary" onClick={ onAddFileClick }>
						{ __( 'Add file', 'redirection' ) }
					</button>
				}
				wrapped={ false }
			/>
		);
	};

	const classes = clsx( 'dropzone', 'import-source-card', {
		'dropzone-dropped': file !== false,
		'dropzone-importing': importingStatus === 'loading',
		'dropzone-hover': hover,
		'import-source-card--active': activeImportType === 'file' && file !== false,
	} );
	const isSelectable = activeImportType !== 'file' && file !== false;
	const onCardKeyDown = ( event: React.KeyboardEvent< HTMLDivElement > ) => {
		if ( ! isSelectable ) {
			return;
		}

		if ( event.key === 'Enter' || event.key === ' ' ) {
			event.preventDefault();
			onClick();
		}
	};

	return (
		<div
			className={ classes }
			onClick={ isSelectable ? onClick : undefined }
			onKeyDown={ isSelectable ? onCardKeyDown : undefined }
			role={ isSelectable ? 'button' : undefined }
			tabIndex={ isSelectable ? 0 : undefined }
		>
			<input
				ref={ fileInputRef }
				type="file"
				style={ { display: 'none' } }
				onChange={ onFileInputChange }
				accept=".json,.csv,.htaccess"
			/>
			{ file === false ? (
				renderInitialDrop()
			) : (
				<div className="dropzone-selected">
					{ renderSelectedFileCard() }
					<div className="import-source-card__actions">
						<button
							type="button"
							className="button-secondary"
							onClick={ onClearFile }
							disabled={ isImporting }
						>
							{ __( 'Clear file', 'redirection' ) }
						</button>
					</div>
				</div>
			) }
		</div>
	);
}

export default FileDropzone;
