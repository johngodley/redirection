import { useMutation, useQuery, UseMutationOptions, UseQueryOptions, useQueryClient } from '@tanstack/react-query';
import apiFetch from '@wp-plugin-lib/api-fetch';
import { RedirectionApi } from 'lib/api-request';
import { handleApiError } from '../errors';
import { queryKeys } from '../query-keys';
import { useMessageStore } from 'stores';

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
	options?: Omit<
		UseMutationOptions< any, Error, ImportMutationVariables >,
		'mutationFn'
	>
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
	options?: Omit< UseMutationOptions< string, Error, { moduleId: string; format: string } >, 'mutationFn' >
) {
	const { addError, incrementProgress, decrementProgress } = useMessageStore();

	return useMutation( {
		mutationFn: async ( { moduleId, format }: { moduleId: string; format: string } ) => {
			incrementProgress();
			try {
				const response = ( await apiFetch( RedirectionApi.export.file( moduleId, format ) ) ) as {
					data: string;
					total: number;
				};
				return response.data;
			} catch ( error ) {
				decrementProgress();
				throw handleApiError( error );
			}
		},
		onSuccess: () => {
			decrementProgress();
		},
		onError: ( error ) => {
			addError( error.message || 'Export failed' );
		},
		...options,
	} );
}

export type { DuplicateMode, FileImportVariables, ImportMode, ImportMutationVariables, PluginImportVariables };
