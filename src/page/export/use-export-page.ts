import { useEffect, useMemo, useState } from 'react';
import { __ } from '@wordpress/i18n';
import type { CardMetaItem } from 'component/import-export/card';
import { useExport, useExportPreview, useGroupList } from 'lib/api/hooks';
import type { ExportRequestVariables } from 'lib/api/hooks';
import {
	getExportFormatLabel,
	getExportSelectionFilename,
	getExportTypesLabel,
	getRedirectModuleLabel,
	getRedirectScopeLabel,
} from './export-helpers';
import type { Message } from 'stores';
import type {
	ExportAction,
	ExportFormat,
	ExportResult,
	ExportState,
	ExportType,
	ExportTypeOption,
	GroupRow,
	RedirectModule,
	RedirectScopeType,
} from './types';

const EXPORT_TYPES: ExportTypeOption[] = [
	{
		id: 'redirect',
		name: __( 'Redirects', 'redirection' ),
		description: __( 'Export redirect rules in Redirection or server formats.', 'redirection' ),
		formats: [ 'json', 'csv', 'apache', 'nginx' ],
	},
	{
		id: 'log',
		name: __( 'Redirect logs', 'redirection' ),
		description: __( 'Export redirect activity logs.', 'redirection' ),
		formats: [ 'json', 'csv' ],
	},
	{
		id: '404',
		name: __( '404 logs', 'redirection' ),
		description: __( 'Export 404 request logs.', 'redirection' ),
		formats: [ 'json', 'csv' ],
	},
	{
		id: 'group',
		name: __( 'Groups', 'redirection' ),
		description: __( 'Export redirect groups and their module assignments.', 'redirection' ),
		formats: [ 'json', 'csv' ],
	},
	{
		id: 'setting',
		name: __( 'Settings', 'redirection' ),
		description: __(
			'Export a portable subset of plugin settings and site-level Redirection options.',
			'redirection'
		),
		formats: [ 'json' ],
	},
];

interface UseExportPageResult {
	exportTypes: ExportTypeOption[];
	availableFormats: ExportFormat[];
	groupRows: GroupRow[];
	summaryMeta: CardMetaItem[];
	hasAllTypesSelected: boolean;
	hasSelectedTypes: boolean;
	state: ExportState;
	onChange: ( name: 'redirectScopeType' | 'redirectModule' | 'redirectGroup' | 'format', value: string ) => void;
	onToggleType: ( nextType: ExportType ) => void;
	onToggleAllTypes: ( enabled: boolean ) => void;
	onDownload: () => void;
	onView: () => void;
}

function useExportPage(): UseExportPageResult {
	const { data: groupData } = useGroupList( {} );
	const groupRows = useMemo( () => ( groupData?.items ?? [] ) as GroupRow[], [ groupData?.items ] );
	const [ selectedTypes, setSelectedTypes ] = useState< ExportType[] >( [] );
	const [ redirectScopeType, setRedirectScopeType ] = useState< RedirectScopeType >( 'all' );
	const [ redirectModule, setRedirectModule ] = useState< RedirectModule >( 'all' );
	const [ redirectGroup, setRedirectGroup ] = useState( 0 );
	const [ format, setFormat ] = useState< ExportFormat >( 'json' );
	const [ lastResult, setLastResult ] = useState< ExportResult | false >( false );
	const exportMutation = useExport( {
		onSuccess: ( response, variables ) => {
			setLastResult( {
				action: variables.download ? 'download' : 'view',
				types: variables.exportTypes || [ variables.exportType ],
				format: variables.format,
				data: response.data,
				total: response.total,
			} );
		},
	} );
	const hasSelectedTypes = selectedTypes.length > 0;
	const hasAllTypesSelected = selectedTypes.length === EXPORT_TYPES.length;
	const primaryExportType = selectedTypes[ 0 ] || 'redirect';
	const hasRedirectExport = selectedTypes.includes( 'redirect' );
	const previewQuery = useExportPreview(
		{
			exportType: primaryExportType,
			exportTypes: selectedTypes,
			format,
			redirectScopeType,
			redirectModule,
			redirectGroup,
		},
		{
			enabled: false,
		}
	);

	const availableFormats = useMemo< ExportFormat[] >( () => {
		if ( selectedTypes.length === 0 ) {
			return [];
		}

		if ( selectedTypes.length !== 1 ) {
			return [ 'json' ];
		}

		const activeExportType = EXPORT_TYPES.find( ( item ) => item.id === selectedTypes[ 0 ] );

		return activeExportType?.formats ?? [ 'json' ];
	}, [ selectedTypes ] );
	const activeGroup = groupRows.find( ( group ) => group.id === redirectGroup ) || null;
	const isExporting = exportMutation.isPending;
	const isPreviewLoading = previewQuery.isLoading || previewQuery.isFetching;
	const refetchPreview = previewQuery.refetch;
	const currentError = exportMutation.error || null;
	const previewTotal =
		! hasSelectedTypes || previewQuery.isError || typeof previewQuery.data?.total !== 'number'
			? null
			: previewQuery.data.total;
	const previewEstimatedSize =
		! hasSelectedTypes || previewQuery.isError || typeof previewQuery.data?.estimatedSize !== 'number'
			? null
			: previewQuery.data.estimatedSize;
	const summaryMeta: CardMetaItem[] = [
		{
			label: __( 'Export', 'redirection' ),
			value: hasSelectedTypes ? getExportTypesLabel( selectedTypes ) : __( 'No export selected', 'redirection' ),
		},
	];

	if ( hasRedirectExport ) {
		summaryMeta.push( {
			label: __( 'Scope', 'redirection' ),
			value: getRedirectScopeLabel(
				redirectScopeType,
				activeGroup ? activeGroup.name : __( 'Group', 'redirection' )
			),
		} );

		if ( redirectScopeType === 'module' ) {
			summaryMeta.push( {
				label: __( 'Module', 'redirection' ),
				value: getRedirectModuleLabel( redirectModule ),
			} );
		}

		if ( redirectScopeType === 'group' ) {
			summaryMeta.push( {
				label: __( 'Group', 'redirection' ),
				value: activeGroup ? activeGroup.name : __( 'No group selected', 'redirection' ),
			} );
		}
	}

	if ( hasSelectedTypes ) {
		summaryMeta.push( {
			label: __( 'Format', 'redirection' ),
			value: getExportFormatLabel( format ),
		} );
	}

	useEffect( () => {
		if ( redirectGroup === 0 && groupRows[ 0 ] ) {
			setRedirectGroup( groupRows[ 0 ].id );
		}
	}, [ groupRows, redirectGroup ] );

	useEffect( () => {
		if ( availableFormats.length === 0 ) {
			return;
		}

		if ( ! availableFormats.includes( format ) ) {
			setFormat( availableFormats[ 0 ] || 'json' );
		}
	}, [ availableFormats, format ] );

	useEffect( () => {
		if ( ! hasSelectedTypes ) {
			return;
		}

		refetchPreview();
	}, [
		hasSelectedTypes,
		format,
		primaryExportType,
		redirectScopeType,
		redirectModule,
		redirectGroup,
		selectedTypes,
		refetchPreview,
	] );

	const onToggleType = ( nextType: ExportType ) => {
		exportMutation.reset();
		setSelectedTypes( ( current ) => {
			if ( current.includes( nextType ) ) {
				return current.filter( ( item ) => item !== nextType );
			}

			return [ ...current, nextType ];
		} );
		setLastResult( false );
	};

	const onToggleAllTypes = ( enabled: boolean ) => {
		exportMutation.reset();
		setSelectedTypes( enabled ? EXPORT_TYPES.map( ( item ) => item.id ) : [] );
		setLastResult( false );
	};

	const onChange = ( name: 'redirectScopeType' | 'redirectModule' | 'redirectGroup' | 'format', value: string ) => {
		exportMutation.reset();
		if ( name === 'redirectScopeType' ) {
			setRedirectScopeType( value as RedirectScopeType );
		} else if ( name === 'redirectModule' ) {
			setRedirectModule( value as RedirectModule );
		} else if ( name === 'redirectGroup' ) {
			setRedirectGroup( Number( value ) );
		} else {
			setFormat( value as ExportFormat );
		}

		setLastResult( false );
	};

	const runExport = ( action: ExportAction ) => {
		if ( selectedTypes.length === 0 ) {
			return;
		}

		const completionNotice: Message = {
			message:
				action === 'download' ? __( 'Export downloaded', 'redirection' ) : __( 'Export viewed', 'redirection' ),
		};
		const request: ExportRequestVariables = {
			exportType: primaryExportType,
			exportTypes: selectedTypes,
			format,
			redirectScopeType,
			redirectModule,
			redirectGroup,
			download: action === 'download',
			completionNotice,
		};

		if ( action === 'download' ) {
			request.filename = getExportSelectionFilename( selectedTypes, format );
		}

		exportMutation.mutate( request );
	};

	const state: ExportState = {
		selectedTypes,
		redirectScopeType,
		redirectModule,
		redirectGroup,
		format,
		isExporting,
		isPreviewLoading,
		previewTotal,
		previewEstimatedSize,
		currentError,
		lastResult,
	};

	return {
		exportTypes: EXPORT_TYPES,
		availableFormats,
		groupRows,
		summaryMeta,
		hasAllTypesSelected,
		hasSelectedTypes,
		state,
		onChange,
		onToggleType,
		onToggleAllTypes,
		onDownload: () => runExport( 'download' ),
		onView: () => runExport( 'view' ),
	};
}

export default useExportPage;
