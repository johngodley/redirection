import { useState, useEffect, useRef } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { createInterpolateElement, Select } from '@wp-plugin-components';
import clsx from 'clsx';
import { useGroupStore, useIoStore } from 'stores';
import { useGroupList, useImporterList, useFileImport, usePluginImport, useExport } from 'lib/api/hooks';
import { nestedGroups, getExportUrl } from 'lib/wordpress-url';
import { LOGS_TYPE_REDIRECT, LOGS_TYPE_404 } from 'lib/log-constants';
import ExportCSV from 'page/logs/export-csv';
import Importer from './importer';
import './style.scss';

function ImportExport() {
	const [ group, setGroup ] = useState< number >( 0 );
	const [ hover, setHover ] = useState< boolean >( false );
	const [ module, setModule ] = useState< string >( 'all' );
	const [ format, setFormat ] = useState< string >( 'json' );
	const fileInputRef = useRef< HTMLInputElement >( null );

	// Direct property access instead of destructuring
	const groupRows = useGroupStore( ( state ) => state.rows );
	const exportData = useIoStore( ( state ) => state.exportData );
	const exportStatus = useIoStore( ( state ) => state.exportStatus );
	const importers = useIoStore( ( state ) => state.importers );
	const importingStatus = useIoStore( ( state ) => state.importingStatus );
	const file = useIoStore( ( state ) => state.file );
	const lastImport = useIoStore( ( state ) => state.lastImport );
	const { setFile, clearFile, setExportData } = useIoStore();

	// Fetch data on mount
	useGroupList( {} );
	useImporterList();

	const fileImport = useFileImport();
	const pluginImport = usePluginImport();
	const exportMutation = useExport();

	useEffect( () => {
		return () => {
			clearFile();
		};
	}, [ clearFile ] );

	const onView = () => {
		exportMutation.mutate( { moduleId: module, format } );
	};

	const onDownload = () => {
		window.location.href = getExportUrl( module, format );
	};

	const onDragEnter = ( e: React.DragEvent ) => {
		e.preventDefault();
		e.stopPropagation();
		if ( importingStatus !== 'loading' ) {
			setHover( true );
		}
	};

	const onDragLeave = ( e: React.DragEvent ) => {
		e.preventDefault();
		e.stopPropagation();
		setHover( false );
	};

	const onDragOver = ( e: React.DragEvent ) => {
		e.preventDefault();
		e.stopPropagation();
	};

	const onDrop = ( e: React.DragEvent ) => {
		e.preventDefault();
		e.stopPropagation();
		setHover( false );

		if ( importingStatus === 'loading' ) {
			return;
		}

		const files = e.dataTransfer.files;
		if ( files.length > 0 ) {
			setFile( files[ 0 ] || false );
			if ( groupRows.length > 0 && groupRows[ 0 ] ) {
				setGroup( groupRows[ 0 ].id );
			}
		}
	};

	const onFileInputChange = ( e: React.ChangeEvent< HTMLInputElement > ) => {
		const files = e.target.files;
		if ( files && files.length > 0 ) {
			setFile( files[ 0 ] || false );
			if ( groupRows[ 0 ] ) {
				setGroup( groupRows[ 0 ].id );
			}
		}
	};

	const onAddFileClick = () => {
		fileInputRef.current?.click();
	};

	const onImport = () => {
		if ( file ) {
			fileImport.mutate( { file, groupId: group } );
		}
	};

	const onCancel = () => {
		setHover( false );
		clearFile();
		setExportData( false );
		if ( fileInputRef.current ) {
			fileInputRef.current.value = '';
		}
	};

	const onInput = ( event: React.ChangeEvent< HTMLSelectElement > ) => {
		const { target } = event;
		const { name, value } = target;

		if ( name === 'group' ) {
			setGroup( parseInt( value, 10 ) );
		} else if ( name === 'module' ) {
			setModule( value );
			if ( value === 'everything' ) {
				setFormat( 'json' );
			}
		} else if ( name === 'format' ) {
			setFormat( value );
		}
	};

	const renderGroupSelect = () => {
		return (
			<div className="groups">
				{ __( 'Import to group', 'redirection' ) }{ ' ' }
				<Select
					items={ nestedGroups( groupRows as any ) as any }
					name="group"
					value={ String( group ) }
					onChange={ onInput as any }
				/>
			</div>
		);
	};

	const renderInitialDrop = () => {
		return (
			<>
				<h3>{ __( 'Import a CSV, .htaccess, or JSON file.', 'redirection' ) }</h3>
				<p>{ __( "Click 'Add File' or drag and drop here.", 'redirection' ) }</p>

				<button type="button" className="button-secondary" onClick={ onAddFileClick }>
					{ __( 'Add File', 'redirection' ) }
				</button>
			</>
		);
	};

	const renderDropBeforeUpload = () => {
		const isJson = file && file.type === 'application/json';

		return (
			<>
				<h3>{ __( 'File selected', 'redirection' ) }</h3>
				<p>
					<code>{ file && file.name }</code>
				</p>
				{ ! isJson && renderGroupSelect() }
				<button className="button-primary" onClick={ onImport }>
					{ __( 'Upload', 'redirection' ) }
				</button>{ ' ' }
				&nbsp;
				<button className="button-secondary" onClick={ onCancel }>
					{ __( 'Cancel', 'redirection' ) }
				</button>
			</>
		);
	};

	const renderUploading = () => {
		return (
			<>
				<h3>{ __( 'Importing', 'redirection' ) }</h3>

				<p>
					<code>{ file && file.name }</code>
				</p>

				<div className="is-placeholder">
					<div className="wpl-placeholder__loading"></div>
				</div>
			</>
		);
	};

	const renderUploaded = () => {
		return (
			<>
				<h3>{ __( 'Finished importing', 'redirection' ) }</h3>

				<p>
					{ __( 'Total redirects imported:', 'redirection' ) } { lastImport }
				</p>
				{ lastImport === 0 && <p>{ __( 'Double-check the file is the correct format!', 'redirection' ) }</p> }

				<button className="button-secondary" onClick={ onCancel }>
					{ __( 'OK', 'redirection' ) }
				</button>
			</>
		);
	};

	const renderDropzone = () => {
		const classes = clsx( {
			dropzone: true,
			'dropzone-dropped': file !== false,
			'dropzone-importing': importingStatus === 'loading',
			'dropzone-hover': hover,
		} );

		let content;

		if ( importingStatus === 'loading' ) {
			content = renderUploading();
		} else if ( importingStatus === 'success' && lastImport !== false && file === false ) {
			content = renderUploaded();
		} else if ( file === false ) {
			content = renderInitialDrop();
		} else {
			content = renderDropBeforeUpload();
		}

		return (
			<div
				className={ classes }
				onDragEnter={ onDragEnter }
				onDragLeave={ onDragLeave }
				onDragOver={ onDragOver }
				onDrop={ onDrop }
			>
				<input
					ref={ fileInputRef }
					type="file"
					style={ { display: 'none' } }
					onChange={ onFileInputChange }
					accept=".json,.csv,.htaccess"
				/>
				{ content }
			</div>
		);
	};

	const renderExport = ( data: string ) => {
		return (
			<div>
				<textarea className="module-export" rows={ 14 } readOnly={ true } value={ data } />
				<input
					className="button-secondary"
					type="submit"
					value={ __( 'Close', 'redirection' ) }
					onClick={ onCancel }
				/>
			</div>
		);
	};

	const renderExporting = () => {
		return (
			<div className="loader-wrapper loader-textarea">
				<div className="wpl-placeholder__loading"></div>
			</div>
		);
	};

	const doImport = ( plugin: any ) => {
		// Browser confirm is intentional here to guard a destructive import action.
		if (
			// eslint-disable-next-line no-alert
			confirm(
				sprintf(
					// translators: %s is the plugin name
					__( 'Are you sure you want to import from %s?', 'redirection' ),
					plugin.name
				)
			)
		) {
			pluginImport.mutate( plugin.id );
		}
	};

	const renderImporters = ( importerList: any[] ) => {
		return (
			<div>
				<h3>{ __( 'Plugin Importers', 'redirection' ) }</h3>

				<p>
					{ __(
						'The following redirect plugins were detected on your site and can be imported from.',
						'redirection'
					) }
				</p>

				{ importerList.map( ( item, pos ) => (
					<Importer plugin={ item } key={ pos } doImport={ doImport } />
				) ) }
			</div>
		);
	};

	return (
		<div className="import">
			<h2>{ __( 'Import', 'redirection' ) }</h2>

			{ renderDropzone() }

			<p>{ __( 'All imports will be appended to the current database - nothing is merged.', 'redirection' ) }</p>
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

			<h2>{ __( 'Export', 'redirection' ) }</h2>
			<p>
				{ __(
					'Export to CSV, Apache .htaccess, Nginx, or Redirection JSON. The JSON format contains full information, and other formats contain partial information appropriate to the format.',
					'redirection'
				) }
			</p>

			<p className="redirect-export_buttons">
				<select name="module" onChange={ onInput } value={ module }>
					<option value="0">{ __( 'Everything', 'redirection' ) }</option>
					<option value="1">{ __( 'WordPress redirects', 'redirection' ) }</option>
					<option value="2">{ __( 'Apache redirects', 'redirection' ) }</option>
					<option value="3">{ __( 'Nginx redirects', 'redirection' ) }</option>
				</select>

				<select name="format" onChange={ onInput } value={ format }>
					<option value="json">{ __( 'Complete data (JSON)', 'redirection' ) }</option>
					<option value="csv">{ __( 'CSV', 'redirection' ) }</option>
					<option value="apache">{ __( 'Apache .htaccess', 'redirection' ) }</option>
					<option value="nginx">{ __( 'Nginx rewrite rules', 'redirection' ) }</option>
				</select>

				<button className="button-primary" onClick={ onView }>
					{ __( 'View', 'redirection' ) }
				</button>
				<button className="button-secondary" onClick={ onDownload }>
					{ __( 'Download', 'redirection' ) }
				</button>
			</p>

			{ exportStatus === 'loading' && renderExporting() }
			{ exportData && exportStatus !== 'loading' && renderExport( exportData ) }

			<h2>{ __( 'Export Logs', 'redirection' ) }</h2>
			<ExportCSV logType={ LOGS_TYPE_REDIRECT } title={ __( 'Export redirect', 'redirection' ) } />
			<br />
			<ExportCSV logType={ LOGS_TYPE_404 } title={ __( 'Export 404', 'redirection' ) } />

			{ importers.length > 0 && renderImporters( importers ) }
		</div>
	);
}

export default ImportExport;
