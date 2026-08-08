import { useMutation, useQuery, UseMutationOptions, UseQueryOptions, useQueryClient } from '@tanstack/react-query';
import apiFetch from '@wp-plugin-lib/api-fetch';
import { RedirectionApi } from 'lib/api-request';
import { copyText, downloadText } from 'page/export/download';
import { handleApiError } from '../errors';
import { queryKeys } from '../query-keys';
import { useMessageStore } from 'stores';
import type { Message } from 'stores';

type ImportMode = 'preview' | 'import';
type DuplicateMode = 'import' | 'ignore' | 'update';
type FileImportFormat = 'apache' | 'csv' | 'redirects-file' | 'json';
type FileImportVariables = {
	sourceType: 'file';
	mode: ImportMode;
	file: File;
	groupId: number;
	duplicateMode: DuplicateMode;
	deleteSource?: boolean;
	importSections?: string[];
	format?: FileImportFormat | undefined;
};
type PluginImportVariables = {
	sourceType: 'plugin';
	mode: ImportMode;
	pluginId: string | string[];
	groupId: number;
	duplicateMode: DuplicateMode;
	deleteSource?: boolean;
};
type ImportMutationVariables = FileImportVariables | PluginImportVariables;
type ExportType = 'redirect' | 'log' | '404' | 'group' | 'setting';
type ExportFormat = 'json' | 'csv' | 'apache' | 'nginx' | 'redirects-file';
type RedirectScopeType = 'all' | 'module' | 'group';
type ExportRequestVariables = {
	exportType: ExportType;
	format: ExportFormat;
	download?: boolean;
	copy?: boolean;
	filename?: string;
	completionNotice?: string | Message | false;
	redirectScopeType?: RedirectScopeType;
	redirectModule?: string;
	redirectGroup?: number;
	exportTypes?: ExportType[];
	params?: Record< string, unknown >;
};
type ExportPreviewVariables = {
	exportType: ExportType;
	exportTypes?: ExportType[];
	format?: ExportFormat;
	redirectScopeType?: RedirectScopeType;
	redirectModule?: string;
	redirectGroup?: number;
};
type ExportResponse = {
	data: string;
	total: number;
	skipped?: number;
	exported?: number;
};
type ExportPreviewResponse = {
	total: number;
	estimatedSize: number;
};

function shouldUseBundleExport( exportTypes?: ExportType[] ) {
	if ( ! exportTypes || exportTypes.length === 0 ) {
		return false;
	}

	if ( exportTypes.length > 1 ) {
		return true;
	}

	return exportTypes[ 0 ] === 'group' || exportTypes[ 0 ] === 'setting';
}

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
	if ( shouldUseBundleExport( variables.exportTypes ) ) {
		const redirectScopeType = variables.redirectScopeType || 'all';
		const redirectModule = variables.redirectModule || 'all';
		const redirectGroup = variables.redirectGroup || 0;

		return RedirectionApi.export.bundle( {
			types: variables.exportTypes,
			scope_type: redirectScopeType,
			scope_value: getRedirectScopeValue( redirectScopeType, redirectModule, redirectGroup ),
			format: variables.format,
		} );
	}

	if ( variables.exportType === 'redirect' ) {
		if ( variables.params ) {
			return RedirectionApi.export.redirect( {
				...variables.params,
				format: variables.format,
			} );
		}

		const redirectScopeType = variables.redirectScopeType || 'all';
		const redirectModule = variables.redirectModule || 'all';
		const redirectGroup = variables.redirectGroup || 0;

		return RedirectionApi.export.redirect( {
			scope_type: redirectScopeType,
			scope_value: getRedirectScopeValue( redirectScopeType, redirectModule, redirectGroup ),
			format: variables.format,
		} );
	}

	if ( variables.exportType === 'group' ) {
		return RedirectionApi.export.group( variables.format, variables.params || {} );
	}

	if ( variables.exportType === 'log' ) {
		return RedirectionApi.export.log( variables.format, variables.params || {} );
	}

	return RedirectionApi.export.error( variables.format, variables.params || {} );
}

function getExportPreviewRequest( variables: ExportPreviewVariables ) {
	if ( shouldUseBundleExport( variables.exportTypes ) ) {
		const redirectScopeType = variables.redirectScopeType || 'all';
		const redirectModule = variables.redirectModule || 'all';
		const redirectGroup = variables.redirectGroup || 0;

		return RedirectionApi.export.bundlePreview( {
			types: variables.exportTypes,
			scope_type: redirectScopeType,
			scope_value: getRedirectScopeValue( redirectScopeType, redirectModule, redirectGroup ),
			format: variables.format || 'json',
		} );
	}

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

function getDefaultCompletionNotice( variables: ExportRequestVariables ) {
	if ( variables.copy ) {
		return 'Export copied';
	}

	if ( variables.download ) {
		return 'Export downloaded';
	}

	return 'Export viewed';
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
	const { addNotice, incrementProgress, decrementProgress } = useMessageStore();

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
							import_sections: variables.importSections || [],
							format: variables.format,
						} )
					);
				} catch ( error ) {
					throw handleApiError( error );
				}
			}

			if ( variables.mode === 'preview' ) {
				if ( Array.isArray( variables.pluginId ) ) {
					throw new Error( 'Plugin preview only supports a single importer' );
				}

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
					plugin: Array.isArray( variables.pluginId ) ? variables.pluginId : [ variables.pluginId ],
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
				const groupsUpdated = ( data as any )?.groups_updated || 0;
				const groupsIgnored = ( data as any )?.groups_ignored || 0;
				const logsImported = ( data as any )?.logs_imported || 0;
				const errorsImported = ( data as any )?.errors_imported || 0;
				const settingsImported = ( data as any )?.settings_imported || 0;

				addNotice(
					`Imported ${ created } new redirects, updated ${ updated } existing redirects, ignored ${ ignored } redirects, ${ groupsCreated } groups created, ${ groupsUpdated } groups updated, ${ groupsIgnored } groups ignored, ${ logsImported } logs imported, ${ errorsImported } 404 logs imported, ${ settingsImported } settings imported`
				);
				queryClient.invalidateQueries( { queryKey: queryKeys.redirects.lists() } );
				queryClient.invalidateQueries( { queryKey: queryKeys.groups.all } );
				queryClient.invalidateQueries( { queryKey: queryKeys.logs.all } );
				queryClient.invalidateQueries( { queryKey: queryKeys.errors.all } );
				queryClient.invalidateQueries( { queryKey: queryKeys.settings.all } );
			}
		},
		onError: ( _error, variables ) => {
			if ( variables.sourceType === 'file' ) {
				decrementProgress();
			}
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
	const { addNotice, incrementProgress, decrementProgress } = useMessageStore();

	return useMutation( {
		mutationFn: async ( variables ) => {
			incrementProgress();

			try {
				const response = ( await apiFetch( getExportRequest( variables ) ) ) as {
					data: string;
					total: number;
					skipped?: number;
				};

				if ( variables.download && variables.filename ) {
					downloadText( variables.filename, response.data, variables.format );
				}

				if ( variables.copy ) {
					await copyText( response.data );
				}

				return response;
			} catch ( error ) {
				throw handleApiError( error );
			}
		},
		onSuccess: ( _data, variables ) => {
			decrementProgress();

			if ( variables.completionNotice !== false ) {
				addNotice( variables.completionNotice || getDefaultCompletionNotice( variables ) );
			}
		},
		onError: () => {
			decrementProgress();
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
	FileImportFormat,
	FileImportVariables,
	ImportMode,
	ImportMutationVariables,
	PluginImportVariables,
	RedirectScopeType,
};
