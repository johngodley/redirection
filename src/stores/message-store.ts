import { create } from 'zustand';
import { devtools } from 'zustand/middleware';

export interface Message {
	message: string;
	id?: string;
	type?: 'error' | 'notice';
}

export interface MessageStore {
	errors: Message[];
	notices: Message[];
	inProgress: number;
	addError: ( error: string | Message ) => void;
	addNotice: ( notice: string | Message ) => void;
	clearErrors: () => void;
	clearNotices: () => void;
	incrementProgress: () => void;
	decrementProgress: () => void;
	reset: () => void;
}

const initialState = {
	errors: [],
	notices: [],
	inProgress: 0,
};

export const useMessageStore = create< MessageStore >()(
	devtools(
		( set ) => ( {
			...initialState,

			addError: ( error ) =>
				set( ( state ) => ( {
					errors: [
						...state.errors,
						typeof error === 'string' ? { message: error, type: 'error' as const } : error,
					],
				} ) ),

			addNotice: ( notice ) =>
				set( ( state ) => ( {
					notices: [
						...state.notices,
						typeof notice === 'string' ? { message: notice, type: 'notice' as const } : notice,
					],
				} ) ),

			clearErrors: () => set( { errors: [] } ),

			clearNotices: () => set( { notices: [] } ),

			incrementProgress: () => set( ( state ) => ( { inProgress: state.inProgress + 1 } ) ),

			decrementProgress: () => set( ( state ) => ( { inProgress: Math.max( 0, state.inProgress - 1 ) } ) ),

			reset: () => set( initialState ),
		} ),
		{ name: 'MessageStore' }
	)
);
