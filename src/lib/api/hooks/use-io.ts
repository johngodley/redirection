import { useMutation, useQuery, UseMutationOptions, UseQueryOptions, useQueryClient } from '@tanstack/react-query';
import apiFetch from '@wp-plugin-lib/api-fetch';
import { RedirectionApi } from 'lib/api-request';
import { handleApiError } from '../errors';
import { queryKeys } from '../query-keys';
import { useMessageStore } from 'stores';

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
 * Mutation hook for importing redirects from plugins
 * @param options
 */
export function usePluginImport( options?: Omit< UseMutationOptions< any, Error, string[] >, 'mutationFn' > ) {
	const queryClient = useQueryClient();
	const { addNotice, addError } = useMessageStore();

	return useMutation( {
		mutationFn: async ( pluginIds: string[] ) => {
			const response = await apiFetch( RedirectionApi.import.pluginImport( pluginIds ) );
			return response;
		},
		onSuccess: () => {
			addNotice( 'Import completed' );
			queryClient.invalidateQueries( { queryKey: queryKeys.redirects.lists() } );
		},
		onError: ( error ) => {
			addError( error.message || 'Import failed' );
		},
		...options,
	} );
}

/**
 * Mutation hook for importing files
 * @param options
 */
export function useFileImport(
	options?: Omit< UseMutationOptions< any, Error, { file: File; groupId: number } >, 'mutationFn' >
) {
	const queryClient = useQueryClient();
	const { addNotice, addError, incrementProgress, decrementProgress } = useMessageStore();

	return useMutation( {
		mutationFn: async ( { file, groupId }: { file: File; groupId: number } ) => {
			incrementProgress();
			try {
				const response = await apiFetch( RedirectionApi.import.upload( String( groupId ), file ) );
				return response;
			} catch ( error ) {
				decrementProgress();
				throw handleApiError( error );
			}
		},
		onSuccess: ( data ) => {
			decrementProgress();
			const imported = ( data as any )?.imported || 0;
			addNotice( `Imported ${ imported } redirects` );
			queryClient.invalidateQueries( { queryKey: queryKeys.redirects.lists() } );
		},
		onError: ( error ) => {
			addError( error.message || 'Import failed' );
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
