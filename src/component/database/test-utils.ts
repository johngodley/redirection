import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import type { DatabaseState } from '../../stores/settings-store';

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

export function mockSettingsStore( mock: jest.MockedFunction< any >, database: Partial< DatabaseState > ) {
	const state = {
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
	mock.mockImplementation( ( selector: any ) => ( selector ? selector( state ) : state ) );
}
