import { useMutation, useQuery, UseMutationOptions, UseQueryOptions, useQueryClient } from '@tanstack/react-query';
import apiFetch from '@wp-plugin-lib/api-fetch';
import { RedirectionApi } from 'lib/api-request';
import { handleApiError } from '../errors';
import { queryKeys } from '../query-keys';
import { useIoStore, useMessageStore } from 'stores';

/**
 * Query hook for fetching available plugin importers
 * @param options
 */
export function useImporterList( options?: Omit< UseQueryOptions< any >, 'queryKey' | 'queryFn' > ) {
	const { setImporters } = useIoStore();

	return useQuery( {
		queryKey: queryKeys.io.importers(),
		queryFn: async () => {
			try {
				const response = await apiFetch( RedirectionApi.import.pluginList() );
				const importers = ( response as any )?.importers || [];
				setImporters( importers );
				return importers;
			} catch ( error ) {
				throw handleApiError( error );
			}
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
	const { setImportingStatus } = useIoStore();
	const { addNotice, addError } = useMessageStore();

	return useMutation( {
		mutationFn: async ( pluginIds: string[] ) => {
			setImportingStatus( 'loading' );
			try {
				const response = await apiFetch( RedirectionApi.import.pluginImport( pluginIds ) );
				return response;
			} catch ( error ) {
				setImportingStatus( 'error' );
				throw handleApiError( error );
			}
		},
		onSuccess: () => {
			setImportingStatus( 'success' );
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
	const { setImportingStatus, setLastImport, clearFile } = useIoStore();
	const { addNotice, addError, incrementProgress, decrementProgress } = useMessageStore();

	return useMutation( {
		mutationFn: async ( { file, groupId }: { file: File; groupId: number } ) => {
			setImportingStatus( 'loading' );
			incrementProgress();
			try {
				const response = await apiFetch( RedirectionApi.import.upload( String( groupId ), file ) );
				return response;
			} catch ( error ) {
				setImportingStatus( 'error' );
				decrementProgress();
				throw handleApiError( error );
			}
		},
		onSuccess: ( data ) => {
			decrementProgress();
			setImportingStatus( 'success' );
			const imported = ( data as any )?.imported || 0;
			setLastImport( imported );
			clearFile();
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
	const { setExportStatus, setExportData } = useIoStore();
	const { addError, incrementProgress, decrementProgress } = useMessageStore();

	return useMutation( {
		mutationFn: async ( { moduleId, format }: { moduleId: string; format: string } ) => {
			setExportStatus( 'loading' );
			incrementProgress();
			try {
				const response = await apiFetch( RedirectionApi.export.file( moduleId, format ) );
				const data = typeof response === 'string' ? response : JSON.stringify( response, null, 2 );
				return data;
			} catch ( error ) {
				setExportStatus( 'error' );
				decrementProgress();
				throw handleApiError( error );
			}
		},
		onSuccess: ( data ) => {
			decrementProgress();
			setExportStatus( 'success' );
			setExportData( data );
		},
		onError: ( error ) => {
			addError( error.message || 'Export failed' );
		},
		...options,
	} );
}
