import { useMutation, useQuery, UseMutationOptions, UseQueryOptions, useQueryClient } from '@tanstack/react-query';
import apiFetch from '@wp-plugin-lib/api-fetch';
import { RedirectionApi } from 'lib/api-request';
import { downloadText } from 'page/export/download';
import { handleApiError } from '../errors';
import { queryKeys } from '../query-keys';
import { useMessageStore } from 'stores';
import type { Message } from 'stores';

type ImportMode = 'preview' | 'import';
type DuplicateMode = 'import' | 'ignore' | 'update';
type FileImportVariables = {
	sourceType: 'file';
	mode: ImportMode;
	file: File;
	groupId: number;
	duplicateMode: DuplicateMode;
	deleteSource?: boolean;
};
type PluginImportVariables = {
	sourceType: 'plugin';
	mode: ImportMode;
	pluginId: string;
	groupId: number;
	duplicateMode: DuplicateMode;
	deleteSource?: boolean;
};
type ImportMutationVariables = FileImportVariables | PluginImportVariables;
type ExportType = 'redirect' | 'log' | '404';
type ExportFormat = 'json' | 'csv' | 'apache' | 'nginx';
type RedirectScopeType = 'all' | 'module' | 'group';
type ExportRequestVariables = {
	exportType: ExportType;
	format: ExportFormat;
	download?: boolean;
	filename?: string;
	downloadNotice?: string | Message | false;
	redirectScopeType?: RedirectScopeType;
	redirectModule?: string;
	redirectGroup?: number;
};
type ExportPreviewVariables = {
	exportType: ExportType;
	format?: ExportFormat;
	redirectScopeType?: RedirectScopeType;
	redirectModule?: string;
	redirectGroup?: number;
};
type ExportResponse = {
	data: string;
	total: number;
};
type ExportPreviewResponse = {
	total: number;
	estimatedSize: number;
};

function getRedirectScopeValue( redirectScopeType: RedirectScopeType, redirectModule: string, redirectGroup: number ) {
	if ( redirectScopeType === 'group' ) {
		return redirectGroup;
	}

	if ( redirectScopeType === 'module' ) {
		return redirectModule;
	}

	return 'all';
}

function getExportRequest( variables: ExportRequestVariables ) {
	if ( variables.exportType === 'redirect' ) {
		const redirectScopeType = variables.redirectScopeType || 'all';
		const redirectModule = variables.redirectModule || 'all';
		const redirectGroup = variables.redirectGroup || 0;

		return RedirectionApi.export.redirect( {
			scope_type: redirectScopeType,
			scope_value: getRedirectScopeValue( redirectScopeType, redirectModule, redirectGroup ),
			format: variables.format,
		} );
	}

	if ( variables.exportType === 'log' ) {
		return RedirectionApi.export.log( variables.format );
	}

	return RedirectionApi.export.error( variables.format );
}

function getExportPreviewRequest( variables: ExportPreviewVariables ) {
	if ( variables.exportType === 'redirect' ) {
		const redirectScopeType = variables.redirectScopeType || 'all';
		const redirectModule = variables.redirectModule || 'all';
		const redirectGroup = variables.redirectGroup || 0;

		return RedirectionApi.export.redirectPreview( {
			scope_type: redirectScopeType,
			scope_value: getRedirectScopeValue( redirectScopeType, redirectModule, redirectGroup ),
			format: variables.format || 'json',
		} );
	}

	if ( variables.exportType === 'log' ) {
		return RedirectionApi.export.logPreview( { format: variables.format || 'json' } );
	}

	return RedirectionApi.export.errorPreview( { format: variables.format || 'json' } );
}

/**
 * Query hook for fetching available plugin importers
 * @param options
 */
export function useImporterList( options?: Omit< UseQueryOptions< any >, 'queryKey' | 'queryFn' > ) {
	return useQuery( {
		queryKey: queryKeys.io.importers(),
		queryFn: async () => {
			const response = await apiFetch( RedirectionApi.import.pluginList() );
			return ( response as any )?.importers || [];
		},
		...options,
	} );
}

/**
 * Mutation hook for running any import action
 * @param options
 */
export function useImportRunner(
	options?: Omit< UseMutationOptions< any, Error, ImportMutationVariables >, 'mutationFn' >
) {
	const queryClient = useQueryClient();
	const { addNotice, addError, incrementProgress, decrementProgress } = useMessageStore();

	return useMutation( {
		mutationFn: async ( variables ) => {
			if ( variables.sourceType === 'file' ) {
				incrementProgress();
				try {
					return await apiFetch(
						RedirectionApi.import.upload( String( variables.groupId ), variables.file, {
							dry_run: variables.mode === 'preview' ? 1 : 0,
							duplicate_mode: variables.duplicateMode,
							delete_source: variables.deleteSource ? 1 : 0,
						} )
					);
				} catch ( error ) {
					decrementProgress();
					throw handleApiError( error );
				}
			}

			if ( variables.mode === 'preview' ) {
				return await apiFetch(
					RedirectionApi.import.pluginPreview( variables.pluginId, {
						group_id: variables.groupId,
						duplicate_mode: variables.duplicateMode,
						delete_source: variables.deleteSource ? 1 : 0,
					} )
				);
			}

			return await apiFetch(
				RedirectionApi.import.pluginImport( {
					plugin: [ variables.pluginId ],
					group_id: variables.groupId,
					duplicate_mode: variables.duplicateMode,
					delete_source: variables.deleteSource ? 1 : 0,
				} )
			);
		},
		onSuccess: ( data, variables ) => {
			if ( variables.sourceType === 'file' ) {
				decrementProgress();
			}

			if ( variables.mode === 'preview' ) {
				addNotice( 'Preview completed' );
			} else {
				const created = ( data as any )?.created || 0;
				const updated = ( data as any )?.updated || 0;
				const ignored = ( data as any )?.ignored || 0;
				const groupsCreated = ( data as any )?.groups_created || 0;

				addNotice(
					`Imported ${ created } new redirects, updated ${ updated } existing redirects, ignored ${ ignored } duplicates, ${ groupsCreated } groups created`
				);
				queryClient.invalidateQueries( { queryKey: queryKeys.redirects.lists() } );
			}
		},
		onError: ( error, variables ) => {
			if ( variables.sourceType === 'file' ) {
				decrementProgress();
			}

			addError( error.message || ( variables.mode === 'preview' ? 'Preview failed' : 'Import failed' ) );
		},
		...options,
	} );
}

/**
 * Mutation hook for exporting data
 * @param options
 */
export function useExport(
	options?: Omit< UseMutationOptions< ExportResponse, Error, ExportRequestVariables >, 'mutationFn' >
) {
	const { addNotice, addError, incrementProgress, decrementProgress } = useMessageStore();

	return useMutation( {
		mutationFn: async ( variables ) => {
			incrementProgress();

			try {
				const response = ( await apiFetch( getExportRequest( variables ) ) ) as {
					data: string;
					total: number;
				};

				if ( variables.download && variables.filename ) {
					downloadText( variables.filename, response.data, variables.format );
				}

				return response;
			} catch ( error ) {
				decrementProgress();
				throw handleApiError( error );
			}
		},
		onSuccess: ( _data, variables ) => {
			decrementProgress();

			if ( variables.download && variables.downloadNotice !== false ) {
				addNotice( variables.downloadNotice || 'Export downloaded' );
			}
		},
		onError: ( error ) => {
			addError( error.message || 'Export failed' );
		},
		...options,
	} );
}

/**
 * Query hook for exporting preview totals
 * @param variables
 * @param options
 */
export function useExportPreview(
	variables: ExportPreviewVariables,
	options?: Omit< UseQueryOptions< ExportPreviewResponse >, 'queryKey' | 'queryFn' >
) {
	return useQuery( {
		queryKey: [ 'export-preview', variables ],
		queryFn: async () => {
			const response = ( await apiFetch( getExportPreviewRequest( variables ) ) ) as { total?: number };

			return {
				total: Number( response.total ?? 0 ),
				estimatedSize: Number( ( response as { estimated_size?: number } ).estimated_size ?? 0 ),
			};
		},
		...options,
	} );
}

export type {
	DuplicateMode,
	ExportFormat,
	ExportPreviewResponse,
	ExportPreviewVariables,
	ExportRequestVariables,
	ExportResponse,
	ExportType,
	FileImportVariables,
	ImportMode,
	ImportMutationVariables,
	PluginImportVariables,
	RedirectScopeType,
};
