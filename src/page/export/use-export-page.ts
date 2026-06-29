import { useEffect, useMemo, useState } from 'react';
import { __ } from '@wordpress/i18n';
import type { CardMetaItem } from 'component/import-export/card';
import { useExport, useExportPreview, useGroupList } from 'lib/api/hooks';
import {
	getExportFilename,
	getExportFormatLabel,
	getExportTypeLabel,
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
];

function useExportPage() {
	const { data: groupData } = useGroupList( {} );
	const groupRows = useMemo( () => ( groupData?.items ?? [] ) as GroupRow[], [ groupData?.items ] );
	const [ exportType, setExportType ] = useState< ExportType >( 'redirect' );
	const [ redirectScopeType, setRedirectScopeType ] = useState< RedirectScopeType >( 'all' );
	const [ redirectModule, setRedirectModule ] = useState< RedirectModule >( 'all' );
	const [ redirectGroup, setRedirectGroup ] = useState( 0 );
	const [ format, setFormat ] = useState< ExportFormat >( 'json' );
	const [ lastResult, setLastResult ] = useState< ExportResult | false >( false );
	const exportMutation = useExport( {
		onSuccess: ( response, variables ) => {
			setLastResult( {
				action: variables.download ? 'download' : 'view',
				type: variables.exportType,
				format: variables.format,
				data: response.data,
				total: response.total,
			} );
		},
	} );
	const previewQuery = useExportPreview( {
		exportType,
		format,
		redirectScopeType,
		redirectModule,
		redirectGroup,
	} );

	const activeExportType = EXPORT_TYPES.find( ( item ) => item.id === exportType ) || EXPORT_TYPES[ 0 ];
	const availableFormats = activeExportType?.formats || [ 'csv' ];
	const activeGroup = groupRows.find( ( group ) => group.id === redirectGroup ) || null;
	const isExporting = exportMutation.isPending;
	const isPreviewLoading = previewQuery.isLoading || previewQuery.isFetching;
	const previewTotal =
		previewQuery.isError || typeof previewQuery.data?.total !== 'number' ? null : previewQuery.data.total;
	const previewEstimatedSize =
		previewQuery.isError || typeof previewQuery.data?.estimatedSize !== 'number'
			? null
			: previewQuery.data.estimatedSize;
	const summaryMeta: CardMetaItem[] = [
		{
			label: __( 'Export type', 'redirection' ),
			value: getExportTypeLabel( exportType ),
		},
	];

	if ( exportType === 'redirect' ) {
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

	summaryMeta.push( {
		label: __( 'Format', 'redirection' ),
		value: getExportFormatLabel( format ),
	} );

	useEffect( () => {
		if ( redirectGroup === 0 && groupRows[ 0 ] ) {
			setRedirectGroup( groupRows[ 0 ].id );
		}
	}, [ groupRows, redirectGroup ] );

	const onSelectType = ( nextType: ExportType ) => {
		const nextExportType = EXPORT_TYPES.find( ( item ) => item.id === nextType );

		setExportType( nextType );
		setFormat( nextExportType?.formats[ 0 ] || 'csv' );
		setLastResult( false );
	};

	const onChange = ( name: 'redirectScopeType' | 'redirectModule' | 'redirectGroup' | 'format', value: string ) => {
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
		const downloadNotice: Message | false =
			action === 'download' ? { message: __( 'Export downloaded', 'redirection' ) } : false;
		const request = {
			exportType,
			format,
			redirectScopeType,
			redirectModule,
			redirectGroup,
			download: action === 'download',
			downloadNotice,
			...( action === 'download' ? { filename: getExportFilename( exportType, format ) } : {} ),
		};

		exportMutation.mutate( request );
	};

	const state: ExportState = {
		exportType,
		redirectScopeType,
		redirectModule,
		redirectGroup,
		format,
		isExporting,
		isPreviewLoading,
		previewTotal,
		previewEstimatedSize,
		lastResult,
	};

	return {
		exportTypes: EXPORT_TYPES,
		availableFormats,
		groupRows,
		summaryMeta,
		state,
		onChange,
		onSelectType,
		onDownload: () => runExport( 'download' ),
		onView: () => runExport( 'view' ),
	};
}

export default useExportPage;
