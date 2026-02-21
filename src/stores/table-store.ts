import { create } from 'zustand';
import { devtools } from 'zustand/middleware';
import type { TableState, RowId } from 'types';

/**
 * Table Store
 *
 * Central store for all table UI state (pagination, filtering, sorting, selection).
 * This is client-only state - API data lives in TanStack Query cache.
 *
 * Each page (redirects, groups, logs, errors) has its own table state.
 */

export interface TableStoreState {
	// Table state for each page
	redirects: TableState;
	groups: TableState;
	logs: TableState;
	errors: TableState;

	// UI flags
	redirectsAddTop: boolean; // Add new redirects to top of list

	// Actions for redirects table
	setRedirectsTable: ( table: Partial< TableState > ) => void;
	clearRedirectsSelected: () => void;
	setRedirectsSelected: ( items: RowId[] ) => void;
	setRedirectsAddTop: ( addTop: boolean ) => void;
	resetRedirectsTable: () => void;

	// Actions for groups table
	setGroupsTable: ( table: Partial< TableState > ) => void;
	clearGroupsSelected: () => void;
	setGroupsSelected: ( items: RowId[] ) => void;
	resetGroupsTable: () => void;

	// Actions for logs table
	setLogsTable: ( table: Partial< TableState > ) => void;
	clearLogsSelected: () => void;
	setLogsSelected: ( items: RowId[] ) => void;
	resetLogsTable: () => void;

	// Actions for errors table
	setErrorsTable: ( table: Partial< TableState > ) => void;
	clearErrorsSelected: () => void;
	setErrorsSelected: ( items: RowId[] ) => void;
	resetErrorsTable: () => void;

	// Global reset
	reset: () => void;
}

const defaultPerPage = window.Redirectioni10n?.per_page ? parseInt( window.Redirectioni10n.per_page, 10 ) : 25;

// Initial table states for each page
const initialRedirectsTable: TableState = {
	page: 0,
	per_page: defaultPerPage,
	orderby: 'id',
	direction: 'desc',
	selected: [],
	filterBy: {},
	displayType: 'standard',
	displaySelected: [ 'last_count', 'last_access', 'source', 'target', 'code', 'title' ],
	groupBy: '',
};

const initialGroupsTable: TableState = {
	page: 0,
	per_page: defaultPerPage,
	orderby: 'name',
	direction: 'desc',
	selected: [],
	filterBy: {},
	displayType: 'standard',
	displaySelected: [ 'name', 'module', 'redirects' ],
	groupBy: '',
};

const initialLogsTable: TableState = {
	page: 0,
	per_page: defaultPerPage,
	orderby: '',
	direction: 'desc',
	selected: [],
	filterBy: {},
	displayType: 'standard',
	displaySelected: [ 'date', 'url', 'target', 'agent', 'ip' ],
	groupBy: '',
};

const initialErrorsTable: TableState = {
	page: 0,
	per_page: defaultPerPage,
	orderby: '',
	direction: 'desc',
	selected: [],
	filterBy: {},
	displayType: 'standard',
	displaySelected: [ 'date', 'url', 'agent', 'ip' ],
	groupBy: '',
};

const initialState = {
	redirects: initialRedirectsTable,
	groups: initialGroupsTable,
	logs: initialLogsTable,
	errors: initialErrorsTable,
	redirectsAddTop: false,
};

export const useTableStore = create< TableStoreState >()(
	devtools(
		( set ) => ( {
			...initialState,

			// Redirects table actions
			setRedirectsTable: ( table ) =>
				set( ( state ) => ( {
					redirects: { ...state.redirects, ...table },
				} ) ),

			clearRedirectsSelected: () =>
				set( ( state ) => ( {
					redirects: { ...state.redirects, selected: [] },
				} ) ),

			setRedirectsSelected: ( items ) =>
				set( ( state ) => ( {
					redirects: { ...state.redirects, selected: items, selectAll: false },
				} ) ),

			setRedirectsAddTop: ( addTop ) => set( { redirectsAddTop: addTop } ),

			resetRedirectsTable: () => set( { redirects: initialRedirectsTable, redirectsAddTop: false } ),

			// Groups table actions
			setGroupsTable: ( table ) =>
				set( ( state ) => ( {
					groups: { ...state.groups, ...table },
				} ) ),

			clearGroupsSelected: () =>
				set( ( state ) => ( {
					groups: { ...state.groups, selected: [] },
				} ) ),

			setGroupsSelected: ( items ) =>
				set( ( state ) => ( {
					groups: { ...state.groups, selected: items, selectAll: false },
				} ) ),

			resetGroupsTable: () => set( { groups: initialGroupsTable } ),

			// Logs table actions
			setLogsTable: ( table ) =>
				set( ( state ) => ( {
					logs: { ...state.logs, ...table },
				} ) ),

			clearLogsSelected: () =>
				set( ( state ) => ( {
					logs: { ...state.logs, selected: [] },
				} ) ),

			setLogsSelected: ( items ) =>
				set( ( state ) => ( {
					logs: { ...state.logs, selected: items, selectAll: false },
				} ) ),

			resetLogsTable: () => set( { logs: initialLogsTable } ),

			// Errors table actions
			setErrorsTable: ( table ) =>
				set( ( state ) => ( {
					errors: { ...state.errors, ...table },
				} ) ),

			clearErrorsSelected: () =>
				set( ( state ) => ( {
					errors: { ...state.errors, selected: [] },
				} ) ),

			setErrorsSelected: ( items ) =>
				set( ( state ) => ( {
					errors: { ...state.errors, selected: items, selectAll: false },
				} ) ),

			resetErrorsTable: () => set( { errors: initialErrorsTable } ),

			// Global reset
			reset: () => set( initialState ),
		} ),
		{ name: 'TableStore' }
	)
);
