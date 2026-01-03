import { useQuery, type UseQueryOptions } from '@tanstack/react-query';
import apiFetch from '@wp-plugin-lib/api-fetch';
import { RedirectLiApi } from 'lib/api-request';
import { queryKeys } from '../query-keys';
import { handleApiError } from '../errors';
import { useInfoStore } from 'stores';
import type { IpInfo, UserAgentInfo, HttpInfo } from 'types';

/**
 * Query hook for fetching IP geolocation
 * @param ip
 * @param options
 */
export function useIpInfo( ip: string, options?: Omit< UseQueryOptions< IpInfo >, 'queryKey' | 'queryFn' > ) {
	const { setMap, setStatus } = useInfoStore();

	return useQuery( {
		queryKey: queryKeys.info.ip( ip ),
		queryFn: async () => {
			setStatus( 'loading' );
			try {
				const response = await apiFetch( RedirectLiApi.ip.getGeo( ip ) );
				const data = response as IpInfo;
				setMap( ip, data );
				setStatus( 'success' );
				return data;
			} catch ( error ) {
				setStatus( 'error' );
				throw handleApiError( error );
			}
		},
		enabled: !! ip,
		...options,
	} );
}

/**
 * Query hook for fetching user agent info
 * @param agent
 * @param options
 */
export function useUserAgentInfo(
	agent: string,
	options?: Omit< UseQueryOptions< UserAgentInfo >, 'queryKey' | 'queryFn' >
) {
	const { setAgent, setStatus } = useInfoStore();

	return useQuery( {
		queryKey: queryKeys.info.agent( agent ),
		queryFn: async () => {
			setStatus( 'loading' );
			try {
				const response = await apiFetch( RedirectLiApi.agent.get( agent ) );
				const data = response as UserAgentInfo;
				setAgent( agent, data );
				setStatus( 'success' );
				return data;
			} catch ( error ) {
				setStatus( 'error' );
				throw handleApiError( error );
			}
		},
		enabled: !! agent,
		...options,
	} );
}

/**
 * Query hook for checking HTTP headers
 * @param url
 * @param options
 */
export function useHttpCheck( url: string, options?: Omit< UseQueryOptions< HttpInfo >, 'queryKey' | 'queryFn' > ) {
	const { setHttp, setStatus } = useInfoStore();

	return useQuery( {
		queryKey: queryKeys.info.http( url ),
		queryFn: async () => {
			setStatus( 'loading' );
			try {
				const response = await apiFetch( RedirectLiApi.http.get( url ) );
				const data = response as HttpInfo;
				setHttp( data );
				setStatus( 'success' );
				return data;
			} catch ( error ) {
				setStatus( 'error' );
				setHttp( false );
				throw handleApiError( error );
			}
		},
		enabled: !! url,
		...options,
	} );
}

/**
 * Clear HTTP check data
 */
export function useClearHttp() {
	const { clearHttp } = useInfoStore();
	return clearHttp;
}
