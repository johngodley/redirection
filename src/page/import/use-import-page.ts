import { useEffect, useRef, useState } from 'react';
import { sprintf, __ } from '@wordpress/i18n';
import { isJsonFile, sniffImportFile } from 'component/import-export/import-sniff';
import { useGroupList, useImporterList, useImportRunner } from 'lib/api/hooks';
import type { DuplicateMode, ImportMode, ImportMutationVariables } from 'lib/api/hooks';
import type { ImportPlugin, ImportState, ImportStats } from './types';

type ImportResponse = Partial< ImportStats > & {
	preview?: ImportStats[ 'preview' ];
};

function getEmptyImportResult(): ImportStats {
	return {
		created: 0,
		updated: 0,
		ignored: 0,
		groups_created: 0,
		groups_imported: 0,
		logs_imported: 0,
		errors_imported: 0,
		settings_imported: 0,
		preview: [],
	};
}

function isDestructivePluginImport( request: ImportMutationVariables ) {
	return (
		request.sourceType === 'plugin' &&
		request.mode === 'import' &&
		request.deleteSource === true &&
		( request.pluginId === 'wordpress-old-slugs' || request.pluginId === 'safe-redirect-manager' )
	);
}

function useImportPage() {
	const [ activeImportType, setActiveImportType ] = useState< 'file' | 'plugin' | null >( null );
	const [ activePluginId, setActivePluginId ] = useState< string | null >( null );
	const [ group, setGroup ] = useState< number >( 0 );
	const [ hover, setHover ] = useState< boolean >( false );
	const [ file, setFile ] = useState< File | false >( false );
	const [ duplicateMode, setDuplicateMode ] = useState< DuplicateMode >( 'import' );
	const [ deleteSource, setDeleteSource ] = useState< boolean >( false );
	const [ selectedSections, setSelectedSections ] = useState< string[] >( [] );
	const [ fileInfo, setFileInfo ] = useState< ImportState[ 'fileInfo' ] >( null );
	const [ isSniffing, setIsSniffing ] = useState< boolean >( false );
	const [ lastImport, setLastImport ] = useState< ImportStats | false >( false );
	const [ lastImportWasDryRun, setLastImportWasDryRun ] = useState< boolean | null >( null );
	const fileInputRef = useRef< HTMLInputElement >( null );
	const dragDepthRef = useRef< number >( 0 );

	const { data: groupData } = useGroupList( {} );
	const groupRows = ( groupData?.items ?? [] ) as ImportState[ 'groupRows' ];
	const { data: importerData = [], isLoading: isLoadingImporters } = useImporterList();
	const importers = importerData as ImportPlugin[];

	const importRunner = useImportRunner( {
		onSuccess: ( data, variables ) => {
			const isPreview = variables.mode === 'preview';
			const response = data as ImportResponse;

			setLastImport( {
				...getEmptyImportResult(),
				...response,
				preview: response.preview || [],
			} );
			setLastImportWasDryRun( isPreview );
		},
	} );

	const isImporting = importRunner.isPending;
	const currentError = importRunner.error || null;
	const hasCompletedImport = lastImport !== false && lastImportWasDryRun === false;
	const activePlugin = importers.find( ( item ) => item.id === activePluginId ) || null;
	const hasActiveImport = activeImportType === 'file' ? file !== false : activePlugin !== null;
	const previewSupported = activeImportType === 'file' ? true : activePlugin?.preview_supported === true;

	let importingStatus = 'idle';
	if ( isImporting ) {
		importingStatus = 'loading';
	} else if ( importRunner.isSuccess ) {
		importingStatus = 'success';
	}

	useEffect( () => {
		return () => {
			setFile( false );
			setActiveImportType( null );
			setActivePluginId( null );
			setLastImport( false );
			setLastImportWasDryRun( null );
		};
	}, [] );

	useEffect( () => {
		let isMounted = true;

		if ( file === false ) {
			setFileInfo( null );
			setIsSniffing( false );
			setSelectedSections( [] );
			return;
		}

		setIsSniffing( true );
		setFileInfo( null );

		sniffImportFile( file )
			.then( ( result ) => {
				if ( isMounted ) {
					setFileInfo( result );
					if ( result.format === 'json' && result.valid && result.contents ) {
						setSelectedSections( Object.keys( result.contents ) );
					}
				}
			} )
			.catch( () => {
				if ( isMounted ) {
					setFileInfo( {
						format: isJsonFile( file ) ? 'json' : 'other',
						valid: false,
						error: 'read-failed',
					} );
				}
			} )
			.finally( () => {
				if ( isMounted ) {
					setIsSniffing( false );
				}
			} );

		return () => {
			isMounted = false;
		};
	}, [ file ] );

	const selectFile = ( selectedFile: File | false ) => {
		importRunner.reset();
		setLastImport( false );
		setLastImportWasDryRun( null );
		setFile( selectedFile );
		setActiveImportType( 'file' );
		setActivePluginId( null );
		setSelectedSections( [] );
		if ( selectedFile && isJsonFile( selectedFile ) ) {
			setGroup( 0 );
		} else if ( groupRows[ 0 ] ) {
			setGroup( groupRows[ 0 ].id );
		}
	};

	const onDragEnter = ( e: React.DragEvent ) => {
		e.preventDefault();
		e.stopPropagation();

		if ( importingStatus !== 'loading' && e.dataTransfer.types.includes( 'Files' ) ) {
			dragDepthRef.current += 1;
			setHover( true );
		}
	};

	const onDragLeave = ( e: React.DragEvent ) => {
		e.preventDefault();
		e.stopPropagation();

		if ( dragDepthRef.current > 0 ) {
			dragDepthRef.current -= 1;
		}

		if ( dragDepthRef.current === 0 ) {
			setHover( false );
		}
	};

	const onDragOver = ( e: React.DragEvent ) => {
		e.preventDefault();
		e.stopPropagation();
	};

	const onDrop = ( e: React.DragEvent ) => {
		e.preventDefault();
		e.stopPropagation();

		dragDepthRef.current = 0;
		setHover( false );

		if ( importingStatus === 'loading' ) {
			return;
		}

		if ( e.dataTransfer.files.length > 0 ) {
			selectFile( e.dataTransfer.files[ 0 ] || false );
		}
	};

	const onFileInputChange = ( e: React.ChangeEvent< HTMLInputElement > ) => {
		const files = e.target.files;
		if ( files && files.length > 0 ) {
			selectFile( files[ 0 ] || false );
		}
	};

	const onAddFileClick = () => {
		fileInputRef.current?.click();
	};

	const onSelectFileImporter = () => {
		if ( file !== false ) {
			importRunner.reset();
			setActiveImportType( 'file' );
			setActivePluginId( null );
			setLastImport( false );
			setLastImportWasDryRun( null );
		}
	};

	const getImportRequest = ( mode: ImportMode ): ImportMutationVariables | null => {
		if ( activeImportType === 'file' && file ) {
			return {
				sourceType: 'file',
				mode,
				file,
				groupId: group,
				duplicateMode,
				deleteSource,
				importSections: selectedSections,
			};
		}

		if ( activeImportType === 'plugin' && activePlugin ) {
			return {
				sourceType: 'plugin',
				mode,
				pluginId: activePlugin.id,
				groupId: group,
				duplicateMode,
				deleteSource,
			};
		}

		return null;
	};

	const onImport = ( dryRun = false ) => {
		const mode: ImportMode = dryRun ? 'preview' : 'import';
		const request = getImportRequest( mode );
		const pluginName = activePlugin?.name || '';

		if ( ! request ) {
			return;
		}

		const confirmMessage = isDestructivePluginImport( request )
			? __( 'This will import the redirects and delete the original data. Are you sure?', 'redirection' )
			: sprintf(
					// translators: %s is the plugin name
					__( 'Are you sure you want to import from %s?', 'redirection' ),
					pluginName
			  );

		if (
			request.sourceType === 'plugin' &&
			request.mode === 'import' &&
			! (
				// Browser confirm is intentional here to guard a destructive import action.
				// eslint-disable-next-line no-alert
				confirm( confirmMessage )
			)
		) {
			return;
		}

		setLastImport( false );
		setLastImportWasDryRun( null );
		importRunner.reset();
		importRunner.mutate( request );
	};

	const onClearFile = () => {
		importRunner.reset();
		setHover( false );
		setFile( false );
		if ( activeImportType === 'file' ) {
			setActiveImportType( null );
		}
		setLastImport( false );
		setLastImportWasDryRun( null );
		if ( fileInputRef.current ) {
			fileInputRef.current.value = '';
		}
	};

	const onCancel = () => {
		importRunner.reset();
		setActiveImportType( null );
		setActivePluginId( null );
		setDuplicateMode( 'import' );
		setDeleteSource( false );
		onClearFile();
	};

	const onSelectPlugin = ( plugin: ImportPlugin ) => {
		importRunner.reset();
		setActiveImportType( 'plugin' );
		setActivePluginId( plugin.id );
		setLastImport( false );
		setLastImportWasDryRun( null );

		if ( group === 0 && groupRows[ 0 ] ) {
			setGroup( groupRows[ 0 ].id );
		}
	};

	const onOptionsChange = ( event: React.ChangeEvent< HTMLSelectElement | HTMLInputElement > ) => {
		const { name, value } = event.target;

		if ( name === 'group' ) {
			importRunner.reset();
			setGroup( parseInt( value, 10 ) );
			setLastImport( false );
			setLastImportWasDryRun( null );
		} else if ( name === 'duplicate_mode' ) {
			importRunner.reset();
			setDuplicateMode( value as DuplicateMode );
			setLastImport( false );
			setLastImportWasDryRun( null );
		} else if ( name === 'delete_source' && event.target instanceof HTMLInputElement ) {
			importRunner.reset();
			setDeleteSource( event.target.checked );
			setLastImport( false );
			setLastImportWasDryRun( null );
		} else if ( name.startsWith( 'import_section_' ) && event.target instanceof HTMLInputElement ) {
			const section = name.replace( 'import_section_', '' );

			importRunner.reset();
			setSelectedSections( ( current ) =>
				event.target.checked ? [ ...current, section ] : current.filter( ( item ) => item !== section )
			);
			setLastImport( false );
			setLastImportWasDryRun( null );
		}
	};

	return {
		state: {
			activeImportType,
			activePluginId,
			group,
			hover,
			file,
			duplicateMode,
			deleteSource,
			fileInfo,
			isSniffing,
			currentError,
			selectedSections,
			lastImport,
			lastImportWasDryRun,
			groupRows,
			importers,
			isLoadingImporters,
			isImporting,
			hasCompletedImport,
			hasActiveImport,
			previewSupported,
		},
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
	};
}

export default useImportPage;
