import { create } from 'zustand';
import { devtools } from 'zustand/middleware';
import type { IpInfo, UserAgentInfo, HttpInfo } from 'types';

export type InfoStatus = 'idle' | 'loading' | 'success' | 'error';

export interface InfoStore {
	status: InfoStatus;
	maps: Record< string, IpInfo >;
	agents: Record< string, UserAgentInfo >;
	http: HttpInfo | false;
	error: string | false;
	setStatus: ( status: InfoStatus ) => void;
	setMap: ( ip: string, info: IpInfo ) => void;
	setAgent: ( ua: string, info: UserAgentInfo ) => void;
	setHttp: ( info: HttpInfo | false ) => void;
	setError: ( error: string | false ) => void;
	clearHttp: () => void;
	reset: () => void;
}

const initialState = {
	status: 'idle' as InfoStatus,
	maps: {},
	agents: {},
	http: false as HttpInfo | false,
	error: false as string | false,
};

export const useInfoStore = create< InfoStore >()(
	devtools(
		( set ) => ( {
			...initialState,

			setStatus: ( status ) => set( { status } ),

			setMap: ( ip, info ) =>
				set( ( state ) => ( {
					maps: { ...state.maps, [ ip ]: info },
				} ) ),

			setAgent: ( ua, info ) =>
				set( ( state ) => ( {
					agents: { ...state.agents, [ ua ]: info },
				} ) ),

			setHttp: ( info ) => set( { http: info } ),

			setError: ( error ) => set( { error } ),

			clearHttp: () => set( { http: false } ),

			reset: () => set( initialState ),
		} ),
		{ name: 'InfoStore' }
	)
);
