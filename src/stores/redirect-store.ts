import { create } from 'zustand';
import { devtools } from 'zustand/middleware';
import type { Redirect, TableState } from 'types';

export type RedirectStatus = 'idle' | 'loading' | 'success' | 'error';

export interface RedirectStore {
	rows: Redirect[];
	total: number;
	table: TableState;
	status: RedirectStatus;
	saving: number[];
	addTop: boolean;
	setRows: ( rows: Redirect[] ) => void;
	setTotal: ( total: number ) => void;
	setTable: ( table: Partial< TableState > ) => void;
	setStatus: ( status: RedirectStatus ) => void;
	setSaving: ( ids: number[] ) => void;
	addSaving: ( id: number ) => void;
	removeSaving: ( id: number ) => void;
	setAddTop: ( addTop: boolean ) => void;
	clearSelected: () => void;
	setSelected: ( items: number[], isEverything?: boolean ) => void;
	reset: () => void;
}

const initialTableState: TableState = {
	page: 0,
	per_page: 25,
	orderby: 'id',
	direction: 'desc',
	selected: [],
	filterBy: {},
	displayType: 'standard',
	displaySelected: [ 'hits', 'last_access', 'source', 'target', 'code', 'title' ],
	groupBy: '',
};

const initialState = {
	rows: [],
	total: 0,
	table: initialTableState,
	status: 'idle' as RedirectStatus,
	saving: [],
	addTop: false,
};

export const useRedirectStore = create< RedirectStore >()(
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

			setAddTop: ( addTop ) => set( { addTop } ),

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
		{ name: 'RedirectStore' }
	)
);
