import { create } from 'zustand';
import { devtools, persist } from 'zustand/middleware';

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

export const useSettingsStore = create< SettingsStore >()(
	devtools(
		persist(
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

				setPluginStatus: ( pluginStatus ) =>
					set( ( state ) => ( {
						pluginStatus: { ...state.pluginStatus, ...pluginStatus },
					} ) ),

				reset: () => set( initialState ),
			} ),
			{
				name: 'redirection-settings',
				partialize: ( state ) => ( { values: state.values } ),
			}
		),
		{ name: 'SettingsStore' }
	)
);
