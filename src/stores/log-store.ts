import { create } from 'zustand';
import { devtools } from 'zustand/middleware';
import type { Log, TableState } from 'types';

export type LogStatus = 'idle' | 'loading' | 'success' | 'error';

export interface LogStore {
	rows: Log[];
	total: number;
	table: TableState;
	status: LogStatus;
	saving: number[];
	logType: string;
	requestCount: number;
	setRows: ( rows: Log[] ) => void;
	setTotal: ( total: number ) => void;
	setTable: ( table: Partial< TableState > ) => void;
	setStatus: ( status: LogStatus ) => void;
	setSaving: ( ids: number[] ) => void;
	addSaving: ( id: number ) => void;
	removeSaving: ( id: number ) => void;
	setLogType: ( logType: string ) => void;
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
	status: 'idle' as LogStatus,
	saving: [],
	logType: '',
	requestCount: 0,
};

export const useLogStore = create< LogStore >()(
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

			setLogType: ( logType ) => set( { logType } ),

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
		{ name: 'LogStore' }
	)
);
