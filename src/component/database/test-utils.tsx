import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import type { DatabaseState } from 'stores/settings-store';

interface State {
	database: DatabaseState;
}

type Selector< T > = ( state: State ) => T;
type MockFunction = {
	mockImplementation: ( impl: ( selector?: Selector< unknown > ) => unknown ) => void;
};

export function createQueryClientWrapper() {
	const queryClient = new QueryClient( {
		defaultOptions: {
			queries: { retry: false },
			mutations: { retry: false },
		},
	} );
	return ( { children }: { children: React.ReactNode } ) => (
		<QueryClientProvider client={ queryClient }>{ children }</QueryClientProvider>
	);
}

export function mockSettingsStore( mock: MockFunction, database: Partial< DatabaseState > ) {
	const state: State = {
		database: {
			current: '',
			next: '',
			debug: [],
			reason: '',
			inProgress: false,
			result: 'ok',
			status: 'ok',
			complete: 0,
			...database,
		},
	};
	mock.mockImplementation( ( selector?: Selector< unknown > ) =>
		selector ? selector( state ) : ( state as unknown )
	);
}
