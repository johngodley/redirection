import { create } from 'zustand';
import { devtools, persist } from 'zustand/middleware';

export interface ModalState {
	isOpen: boolean;
	type?: string;
	data?: any;
}

export interface UIStore {
	modal: ModalState;
	sidebarOpen: boolean;
	showDatabase: boolean;
	openModal: ( type: string, data?: any ) => void;
	closeModal: () => void;
	toggleSidebar: () => void;
	setSidebarOpen: ( open: boolean ) => void;
	setShowDatabase: ( show: boolean ) => void;
	reset: () => void;
}

const initialState: Omit<
	UIStore,
	'openModal' | 'closeModal' | 'toggleSidebar' | 'setSidebarOpen' | 'setShowDatabase' | 'reset'
> = {
	modal: {
		isOpen: false,
	},
	sidebarOpen: true,
	showDatabase: false,
};

export const useUIStore = create< UIStore >()(
	devtools(
		persist(
			( set ) => ( {
				...initialState,

				openModal: ( type, data ) =>
					set( {
						modal: {
							isOpen: true,
							type,
							data,
						},
					} ),

				closeModal: () =>
					set( {
						modal: {
							isOpen: false,
						},
					} ),

				toggleSidebar: () => set( ( state ) => ( { sidebarOpen: ! state.sidebarOpen } ) ),

				setSidebarOpen: ( open ) => set( { sidebarOpen: open } ),

				setShowDatabase: ( show ) => set( { showDatabase: show } ),

				reset: () => set( initialState ),
			} ),
			{
				name: 'redirection-ui',
				partialize: ( state ) => ( { sidebarOpen: state.sidebarOpen } ),
			}
		),
		{ name: 'UIStore' }
	)
);
