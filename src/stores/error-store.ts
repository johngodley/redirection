import { create } from 'zustand';
import { devtools } from 'zustand/middleware';
import type { Error404, TableState } from 'types';

export type ErrorStatus = 'idle' | 'loading' | 'success' | 'error';

export interface ErrorStore {
	rows: Error404[];
	total: number;
	table: TableState;
	status: ErrorStatus;
	saving: number[];
	requestCount: number;
	setRows: ( rows: Error404[] ) => void;
	setTotal: ( total: number ) => void;
	setTable: ( table: Partial< TableState > ) => void;
	setStatus: ( status: ErrorStatus ) => void;
	setSaving: ( ids: number[] ) => void;
	addSaving: ( id: number ) => void;
	removeSaving: ( id: number ) => void;
	incrementRequestCount: () => void;
	clearSelected: () => void;
	setSelected: ( items: number[], isEverything?: boolean ) => void;
	reset: () => void;
}

const initialTableState: TableState = {
	page: 0,
	per_page: 25,
	orderby: '',
	direction: 'desc',
	selected: [],
	filterBy: {},
	displayType: 'standard',
	displaySelected: [ 'ip', 'url', 'referrer', 'agent', 'date' ],
	groupBy: '',
};

const initialState = {
	rows: [],
	total: 0,
	table: initialTableState,
	status: 'idle' as ErrorStatus,
	saving: [],
	requestCount: 0,
};

export const useErrorStore = create< ErrorStore >()(
	devtools(
		( set ) => ( {
			...initialState,

			setRows: ( rows ) => set( { rows } ),

			setTotal: ( total ) => set( { total } ),

			setTable: ( table ) =>
				set( ( state ) => ( {
					table: { ...state.table, ...table },
				} ) ),

			setStatus: ( status ) => set( { status } ),

			setSaving: ( ids ) => set( { saving: ids } ),

			addSaving: ( id ) =>
				set( ( state ) => ( {
					saving: state.saving.includes( id ) ? state.saving : [ ...state.saving, id ],
				} ) ),

			removeSaving: ( id ) =>
				set( ( state ) => ( {
					saving: state.saving.filter( ( savedId ) => savedId !== id ),
				} ) ),

			incrementRequestCount: () =>
				set( ( state ) => ( {
					requestCount: state.requestCount + 1,
				} ) ),

			clearSelected: () =>
				set( ( state ) => ( {
					table: { ...state.table, selected: [] },
				} ) ),

			setSelected: ( items ) =>
				set( ( state ) => ( {
					table: { ...state.table, selected: items },
				} ) ),

			reset: () => set( initialState ),
		} ),
		{ name: 'ErrorStore' }
	)
);
