import { create } from 'zustand';
import { devtools } from 'zustand/middleware';

import type { Group, TableState } from 'types';

export type GroupStatus = 'idle' | 'loading' | 'success' | 'error';

export interface GroupStore {
	rows: Group[];
	total: number;
	table: TableState;
	status: GroupStatus;
	saving: number[];
	setRows: ( rows: Group[] ) => void;
	setTotal: ( total: number ) => void;
	setTable: ( table: Partial< TableState > ) => void;
	setStatus: ( status: GroupStatus ) => void;
	setSaving: ( ids: number[] ) => void;
	addSaving: ( id: number ) => void;
	removeSaving: ( id: number ) => void;
	clearSelected: () => void;
	setSelected: ( items: number[], isEverything?: boolean ) => void;
	reset: () => void;
}

const initialTableState: TableState = {
	page: 0,
	per_page: 25,
	orderby: 'name',
	direction: 'desc',
	selected: [],
	filterBy: {},
	displayType: 'standard',
	displaySelected: [ 'name' ],
	groupBy: '',
};

const initialState = {
	rows: [],
	total: 0,
	table: initialTableState,
	status: 'idle' as GroupStatus,
	saving: [],
};

export const useGroupStore = create< GroupStore >()(
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
		{ name: 'GroupStore' }
	)
);
