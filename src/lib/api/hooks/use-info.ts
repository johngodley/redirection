import { useQuery, type UseQueryOptions } from '@tanstack/react-query';
import apiFetch from '@wp-plugin-lib/api-fetch';
import { RedirectLiApi } from 'lib/api-request';
import { queryKeys } from '../query-keys';
import type { IpInfo, UserAgentInfo, HttpInfo } from 'types';

/**
 * Query hook for fetching IP geolocation
 * @param ip
 * @param options
 */
export function useIpInfo( ip: string, options?: Omit< UseQueryOptions< IpInfo >, 'queryKey' | 'queryFn' > ) {
	return useQuery( {
		queryKey: queryKeys.info.ip( ip ),
		queryFn: async () => {
			const response = await apiFetch( RedirectLiApi.ip.getGeo( ip ) );
			return response as IpInfo;
		},
		enabled: !! ip,
		staleTime: 1000 * 60 * 60 * 24, // 24 hours - IP info doesn't change often
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
	return useQuery( {
		queryKey: queryKeys.info.agent( agent ),
		queryFn: async () => {
			const response = await apiFetch( RedirectLiApi.agent.get( agent ) );
			return response as UserAgentInfo;
		},
		enabled: !! agent,
		staleTime: 1000 * 60 * 60 * 24, // 24 hours - User agent info doesn't change often
		...options,
	} );
}

/**
 * Query hook for checking HTTP headers
 * @param url
 * @param options
 */
export function useHttpCheck( url: string, options?: Omit< UseQueryOptions< HttpInfo >, 'queryKey' | 'queryFn' > ) {
	return useQuery( {
		queryKey: queryKeys.info.http( url ),
		queryFn: async () => {
			const response = await apiFetch( RedirectLiApi.http.get( url ) );
			return response as HttpInfo;
		},
		enabled: !! url,
		staleTime: 0, // HTTP checks should always be fresh
		...options,
	} );
}
