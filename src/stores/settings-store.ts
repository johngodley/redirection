import { create } from 'zustand';
import { devtools } from 'zustand/middleware';

import type { Settings } from 'types';

export type SettingsStatus = 'idle' | 'loading' | 'success' | 'error';

export interface DatabaseState {
	current: string;
	next: string;
	debug: string[];
	reason: string;
	inProgress: boolean;
	result: string;
	status: string;
	complete: number;
}

export interface ApiTestResult {
	status: string;
	error?: any;
	code?: string;
}

export interface ApiTest {
	[ key: string ]: {
		GET: ApiTestResult;
		POST: ApiTestResult;
	};
}

export interface ApiState {
	routes: { [ key: string ]: string };
	current: string;
}

export interface StatusItem {
	name: string;
	status: string;
	message: string;
}

export interface DebugInfo {
	ip_header: { [ key: string ]: string };
	database: { current: string };
}

export interface PluginStatus {
	status: StatusItem[];
	debug: DebugInfo | false;
}

export interface SettingsStore {
	values: Settings | null;
	loadStatus: SettingsStatus;
	saveStatus: boolean;
	error: string | false;
	database: DatabaseState;
	showDatabase: boolean;
	api: ApiState;
	apiTest: ApiTest;
	pluginStatus: PluginStatus;
	setValues: ( values: Settings ) => void;
	updateValues: ( updates: Partial< Settings > ) => void;
	setLoadStatus: ( status: SettingsStatus ) => void;
	setSaveStatus: ( status: boolean ) => void;
	setError: ( error: string | false ) => void;
	setDatabase: ( database: Partial< DatabaseState > ) => void;
	setShowDatabase: ( show: boolean ) => void;
	setApi: ( api: Partial< ApiState > ) => void;
	setApiTest: ( apiTest: Partial< ApiTest > ) => void;
	clearApiTest: () => void;
	setPluginStatus: ( pluginStatus: Partial< PluginStatus > ) => void;
	reset: () => void;
}

const initialDatabaseState: DatabaseState = {
	current: '',
	next: '',
	debug: [],
	reason: '',
	inProgress: false,
	result: 'ok',
	status: 'ok',
	complete: 0,
};

const initialState = {
	values: null,
	loadStatus: 'idle' as SettingsStatus,
	saveStatus: false,
	error: false as string | false,
	database: initialDatabaseState,
	showDatabase: false,
	api: {
		routes: {},
		current: '',
	},
	apiTest: {},
	pluginStatus: {
		status: [],
		debug: false as DebugInfo | false,
	},
};

if ( typeof window !== 'undefined' ) {
	try {
		window.localStorage.removeItem( 'redirection-settings' );
	} catch ( error ) {
		void error;
	}
}

export const useSettingsStore = create< SettingsStore >()(
	devtools(
		( set ) => ( {
			...initialState,

			setValues: ( values ) => set( { values } ),

			updateValues: ( updates ) =>
				set( ( state ) => ( {
					values: state.values ? { ...state.values, ...updates } : null,
				} ) ),

			setLoadStatus: ( status ) => set( { loadStatus: status } ),

			setSaveStatus: ( status ) => set( { saveStatus: status } ),

			setError: ( error ) => set( { error } ),

			setDatabase: ( database ) =>
				set( ( state ) => ( {
					database: { ...state.database, ...database },
				} ) ),

			setShowDatabase: ( show ) => set( { showDatabase: show } ),

			setApi: ( api ) =>
				set( ( state ) => ( {
					api: { ...state.api, ...api },
				} ) ),

			setApiTest: ( apiTest ) =>
				set( ( state ) => {
					const updatedApiTest: ApiTest = { ...state.apiTest };
					Object.keys( apiTest ).forEach( ( key ) => {
						if ( apiTest[ key ] ) {
							updatedApiTest[ key ] = apiTest[ key ];
						}
					} );
					return { apiTest: updatedApiTest };
				} ),

			clearApiTest: () => set( { apiTest: {} } ),

			setPluginStatus: ( pluginStatus ) =>
				set( ( state ) => ( {
					pluginStatus: { ...state.pluginStatus, ...pluginStatus },
				} ) ),

			reset: () => set( initialState ),
		} ),
		{ name: 'SettingsStore' }
	)
);
