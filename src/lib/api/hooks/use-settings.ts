import { useQuery, useMutation, useQueryClient, UseQueryOptions, UseMutationOptions } from '@tanstack/react-query';
import apiFetch from '@wp-plugin-lib/api-fetch';
import { RedirectionApi } from 'lib/api-request';
import { SettingsSchema, type Settings } from 'types';
import { ZodError } from 'zod';
import { queryKeys } from '../query-keys';
import { handleApiError } from '../errors';
import { useSettingsStore, useMessageStore } from 'stores';

/**
 * Query hook for fetching settings
 * @param options
 */
export function useSettings( options?: Omit< UseQueryOptions< Settings >, 'queryKey' | 'queryFn' > ) {
	return useQuery( {
		queryKey: queryKeys.settings.get(),
		queryFn: async () => {
			useSettingsStore.getState().setLoadStatus( 'loading' );
			useSettingsStore.getState().setError( false );

			try {
				const response = await apiFetch( RedirectionApi.setting.get() );
				// Extract settings and merge additional fields from the response
				const settings = ( response as any )?.settings || response;
				const merged = {
					...settings,
					// Map post_types to postTypes to match schema
					postTypes: ( response as any )?.post_types || settings.postTypes,
					installed: ( response as any )?.installed || settings.installed,
					warning: ( response as any )?.warning || settings.warning,
				};

				try {
					const validated = SettingsSchema.parse( merged );

					// Keep settings in Zustand for persistence (persist middleware)
					useSettingsStore.getState().setValues( validated );
					useSettingsStore.getState().setLoadStatus( 'success' );

					return validated;
				} catch ( parseError ) {
					if ( parseError instanceof ZodError ) {
						// Fall back to using the raw settings if validation fails
						useSettingsStore.getState().setValues( merged as Settings );
						useSettingsStore.getState().setLoadStatus( 'success' );
						useMessageStore.getState().addError( 'Settings validation failed, using unvalidated data.' );

						return merged as Settings;
					}

					throw parseError;
				}
			} catch ( error ) {
				useSettingsStore.getState().setLoadStatus( 'error' );
				useSettingsStore.getState().setError( ( error as any ).message || 'Failed to load settings' );
				useMessageStore.getState().addError( ( error as any ).message || 'Failed to load settings' );
				throw handleApiError( error );
			}
		},
		...options,
	} );
}

/**
 * Mutation hook for updating settings
 * @param options
 */
export function useSettingsUpdate(
	options?: Omit< UseMutationOptions< Settings, Error, Partial< Settings > >, 'mutationFn' >
) {
	const queryClient = useQueryClient();
	const { updateValues, setSaveStatus } = useSettingsStore();
	const { incrementProgress, decrementProgress, addNotice, addError } = useMessageStore();

	return useMutation( {
		mutationFn: async ( updates: Partial< Settings > ) => {
			incrementProgress();
			setSaveStatus( true );
			try {
				const response = await apiFetch( RedirectionApi.setting.update( updates ) );
				const settings = ( response as any )?.settings || response;
				return SettingsSchema.parse( settings );
			} catch ( error ) {
				decrementProgress();
				setSaveStatus( false );
				throw handleApiError( error );
			}
		},
		onSuccess: ( data ) => {
			decrementProgress();
			setSaveStatus( false );
			updateValues( data );
			addNotice( 'Settings saved' );
			queryClient.invalidateQueries( { queryKey: queryKeys.settings.all } );
		},
		onError: ( error ) => {
			addError( error.message || 'Failed to save settings' );
		},
		...options,
	} );
}

/**
 * Mutation hook for database upgrade
 * @param options
 */
export function useDatabaseUpgrade( options?: Omit< UseMutationOptions< any, Error, string >, 'mutationFn' > ) {
	const { setDatabase } = useSettingsStore();
	const { incrementProgress, decrementProgress, addError } = useMessageStore();

	return useMutation( {
		mutationFn: async ( action: string ) => {
			incrementProgress();
			setDatabase( { inProgress: true } );
			try {
				const response = await apiFetch(
					RedirectionApi.plugin.upgradeDatabase(
						action === 'skip' || action === 'stop' ? { upgrade: action } : undefined
					)
				);
				return response;
			} catch ( error ) {
				decrementProgress();
				setDatabase( { inProgress: false, result: 'failed' } );
				throw handleApiError( error );
			}
		},
		onSuccess: ( data: any ) => {
			decrementProgress();
			setDatabase( {
				inProgress: false,
				...data,
			} );
		},
		onError: ( error ) => {
			addError( error.message || 'Database upgrade failed' );
		},
		...options,
	} );
}

/**
 * Mutation hook for checking API endpoints
 * @param options
 */
export function useApiCheck(
	options?: Omit< UseMutationOptions< any, Error, { id: string; url: string }[] >, 'mutationFn' >
) {
	const { setApiTest } = useSettingsStore();

	return useMutation( {
		mutationFn: async ( apis: { id: string; url: string }[] ) => {
			const results: any = {};

			for ( const api of apis ) {
				const { id } = api;
				results[ id ] = {
					GET: { status: 'loading' },
					POST: { status: 'loading' },
				};
			}

			setApiTest( results );

			// Test each API endpoint
			for ( const api of apis ) {
				const { id, url } = api;
				// Append the test endpoint to the base URL
				const testUrl = url.endsWith( '/' )
					? `${ url }redirection/v1/plugin/test`
					: `${ url }/redirection/v1/plugin/test`;

				// Test GET
				try {
					await apiFetch( { url: testUrl, method: 'GET' } );
					results[ id ].GET = { status: 'ok' };
				} catch ( error: any ) {
					results[ id ].GET = {
						status: 'fail',
						error,
						code: error?.data?.status || error?.code,
					};
				}

				setApiTest( results );

				// Test POST
				try {
					await apiFetch( { url: testUrl, method: 'POST', data: {} } );
					results[ id ].POST = { status: 'ok' };
				} catch ( error: any ) {
					results[ id ].POST = {
						status: 'fail',
						error,
						code: error?.data?.status || error?.code,
					};
				}

				setApiTest( results );
			}

			return results;
		},
		...options,
	} );
}

/**
 * Mutation hook for finishing upgrade
 * @param options
 */
export function useFinishUpgrade( options?: Omit< UseMutationOptions< any, Error, void >, 'mutationFn' > ) {
	const queryClient = useQueryClient();
	const { setShowDatabase } = useSettingsStore();
	const { addError } = useMessageStore();

	return useMutation( {
		mutationFn: async () => {
			try {
				const response = await apiFetch( RedirectionApi.plugin.finishSetup() );
				return response;
			} catch ( error ) {
				throw handleApiError( error );
			}
		},
		onSuccess: () => {
			setShowDatabase( false );
			queryClient.invalidateQueries( { queryKey: queryKeys.settings.all } );
			// Reload to show normal admin interface
			window.location.href = window.Redirectioni10n.pluginRoot;
		},
		onError: ( error ) => {
			addError( error.message || 'Failed to finish setup' );
		},
		...options,
	} );
}

/**
 * Mutation hook for fixing database status
 * @param options
 */
export function useFixStatus(
	options?: Omit< UseMutationOptions< any, Error, { reason: string; current: string }, unknown >, 'mutationFn' >
) {
	const queryClient = useQueryClient();
	const { setDatabase } = useSettingsStore();

	// Extract onSuccess from options to compose it
	const { onSuccess: onSuccessCallback, ...restOptions } = options || {};

	return useMutation( {
		mutationFn: async ( { reason, current }: { reason: string; current: string } ) => {
			const response = await apiFetch( RedirectionApi.plugin.fixStatus( reason, current ) );
			return response;
		},
		onSuccess: ( ...args: Parameters< NonNullable< typeof onSuccessCallback > > ) => {
			// Always run core success behavior
			const [ data ] = args;
			setDatabase( ( data as any ).database || {} );
			queryClient.invalidateQueries( { queryKey: queryKeys.settings.all } );

			// Then invoke caller's onSuccess if provided
			onSuccessCallback?.( ...args );
		},
		...restOptions,
	} );
}

/**
 * Query hook for fetching plugin status
 * @param options
 */
export function usePluginStatus( options?: Omit< UseQueryOptions< any >, 'queryKey' | 'queryFn' > ) {
	return useQuery( {
		queryKey: queryKeys.settings.status(),
		queryFn: async () => {
			try {
				const response = await apiFetch( RedirectionApi.plugin.status() );
				useSettingsStore.getState().setPluginStatus( response as any );
				return response;
			} catch ( error ) {
				throw handleApiError( error );
			}
		},
		...options,
	} );
}

/**
 * Mutation hook for deleting the plugin
 * @param options
 */
export function usePluginDelete( options?: Omit< UseMutationOptions< any, Error, void >, 'mutationFn' > ) {
	const { incrementProgress, decrementProgress, addNotice, addError } = useMessageStore();

	return useMutation( {
		mutationFn: async () => {
			incrementProgress();
			try {
				const response = await apiFetch( RedirectionApi.plugin.delete() );
				return response;
			} catch ( error ) {
				decrementProgress();
				throw handleApiError( error );
			}
		},
		onSuccess: () => {
			decrementProgress();
			addNotice( 'Plugin deleted' );
			// Redirect to plugins page after deletion
			window.location.href = '/wp-admin/plugins.php';
		},
		onError: ( error ) => {
			addError( error.message || 'Failed to delete plugin' );
		},
		...options,
	} );
}
