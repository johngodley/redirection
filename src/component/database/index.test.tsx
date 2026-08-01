import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import Database from './index';
import * as stores from 'stores';
import * as useSettings from 'lib/api/hooks/use-settings';
import type { DatabaseState } from '../../stores/settings-store';

// Mock modules using path aliases that match the component imports
jest.mock( 'stores', () => ( {
	useSettingsStore: jest.fn(),
	useTableStore: jest.fn(),
	useMessageStore: jest.fn(),
	useUIStore: jest.fn(),
} ) );
jest.mock( 'lib/api/hooks/use-settings', () => ( {
	useDatabaseUpgrade: jest.fn(),
	useSettings: jest.fn(),
	useSettingsUpdate: jest.fn(),
} ) );
jest.mock(
	'../../component/prevent-leave',
	() =>
		( { prevent, message }: any ) =>
			prevent ? <div data-testid="prevent-leave">{ message }</div> : null
);
jest.mock( './database-error', () => ( { error, onRetry }: any ) => (
	<div data-testid="database-error">
		<div>{ error }</div>
		<button onClick={ onRetry }>Retry</button>
	</div>
) );
jest.mock( './api-error', () => ( { onRetry }: any ) => (
	<div data-testid="api-error">
		<button onClick={ onRetry }>Retry</button>
	</div>
) );
jest.mock( '@wp-plugin-components', () => ( {
	Spinner: () => <div data-testid="spinner">Loading...</div>,
} ) );

const mockUseSettingsStore = stores.useSettingsStore as jest.MockedFunction< typeof stores.useSettingsStore >;
const mockUseDatabaseUpgrade = useSettings.useDatabaseUpgrade as jest.MockedFunction<
	typeof useSettings.useDatabaseUpgrade
>;

function createWrapper() {
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

const mockSetDatabase = jest.fn();

// Helper to mock the settings store with database state. Returns the mutable state
// object so tests can simulate what a later poll "sees" via useSettingsStore.getState().
function mockDatabaseState( database: Partial< DatabaseState > ) {
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
		setDatabase: mockSetDatabase,
	};
	mockUseSettingsStore.mockImplementation( ( selector: any ) => {
		return selector ? selector( state ) : state;
	} );
	( mockUseSettingsStore as any ).getState = jest.fn( () => state );

	return state;
}

describe( 'Database Component', () => {
	const mockMutate = jest.fn();
	const mockMutateAsync = jest.fn().mockResolvedValue( undefined );

	beforeEach( () => {
		jest.clearAllMocks();
		jest.useFakeTimers();
		mockMutateAsync.mockResolvedValue( undefined );

		mockUseDatabaseUpgrade.mockReturnValue( {
			mutate: mockMutate,
			mutateAsync: mockMutateAsync,
		} as any );
	} );

	afterEach( () => {
		jest.runOnlyPendingTimers();
		jest.useRealTimers();
	} );

	describe( 'Initial Installation (need-install)', () => {
		it( 'should display "Setting up Redirection" title for initial installation', () => {
			mockDatabaseState( {
				status: 'need-install',
				result: 'ok',
				complete: 0,
			} );

			render( <Database />, { wrapper: createWrapper() } );

			expect( screen.getByText( 'Setting up Redirection' ) ).toBeInTheDocument();
		} );

		it( 'should automatically trigger upgrade after delay when work is needed', async () => {
			mockDatabaseState( {
				status: 'need-install',
				result: 'ok',
				complete: 25,
				reason: 'Installing tables',
			} );

			render( <Database />, { wrapper: createWrapper() } );

			expect( mockMutateAsync ).not.toHaveBeenCalled();

			// Fast-forward time by 1 second (DELAY = 1000)
			await jest.advanceTimersByTimeAsync( 1000 );

			expect( mockMutateAsync ).toHaveBeenCalledWith( '' );
		} );

		it( 'should show progress bar with correct percentage', () => {
			mockDatabaseState( {
				status: 'need-install',
				result: 'ok',
				complete: 45,
			} );

			render( <Database />, { wrapper: createWrapper() } );

			expect( screen.getByText( 'Progress: 45%' ) ).toBeInTheDocument();
		} );

		it( 'should display reason text when provided', () => {
			mockDatabaseState( {
				status: 'need-install',
				result: 'ok',
				complete: 50,
				reason: 'Creating redirect table',
			} );

			render( <Database />, { wrapper: createWrapper() } );

			expect( screen.getByText( 'Creating redirect table' ) ).toBeInTheDocument();
		} );

		it( 'should show spinner when loading', () => {
			mockDatabaseState( {
				status: 'need-install',
				result: 'ok',
				complete: 75,
			} );

			render( <Database />, { wrapper: createWrapper() } );

			expect( screen.getByTestId( 'spinner' ) ).toBeInTheDocument();
		} );
	} );

	describe( 'Database Update (need-update)', () => {
		it( 'should display "Upgrading Redirection" title for updates', () => {
			mockDatabaseState( {
				status: 'need-update',
				result: 'ok',
				complete: 0,
				current: '5.4.0',
				next: '5.5.0',
			} );

			render( <Database />, { wrapper: createWrapper() } );

			expect( screen.getByText( 'Upgrading Redirection' ) ).toBeInTheDocument();
		} );

		it( 'should automatically trigger upgrade for updates', async () => {
			mockDatabaseState( {
				status: 'need-update',
				result: 'ok',
				complete: 0,
				current: '5.4.0',
				next: '5.5.0',
			} );

			render( <Database />, { wrapper: createWrapper() } );

			await jest.advanceTimersByTimeAsync( 1000 );

			expect( mockMutateAsync ).toHaveBeenCalledWith( '' );
		} );
	} );

	describe( 'Completion States', () => {
		it( 'should render children when installation is finished', () => {
			mockDatabaseState( {
				status: 'finish-install',
				result: 'ok',
				complete: 100,
			} );

			render(
				<Database>
					<div data-testid="child-content">Installation Complete!</div>
				</Database>,
				{ wrapper: createWrapper() }
			);

			expect( screen.getByTestId( 'child-content' ) ).toBeInTheDocument();
		} );

		it( 'should render children when update is finished', () => {
			mockDatabaseState( {
				status: 'finish-update',
				result: 'ok',
				complete: 100,
			} );

			render(
				<Database>
					<div data-testid="child-content">Update Complete!</div>
				</Database>,
				{ wrapper: createWrapper() }
			);

			expect( screen.getByTestId( 'child-content' ) ).toBeInTheDocument();
		} );

		it( 'should not show spinner when finished', () => {
			mockDatabaseState( {
				status: 'finish-install',
				result: 'ok',
				complete: 100,
			} );

			render( <Database />, { wrapper: createWrapper() } );

			expect( screen.queryByTestId( 'spinner' ) ).not.toBeInTheDocument();
		} );

		it( 'should not trigger upgrade when finished', async () => {
			mockDatabaseState( {
				status: 'finish-install',
				result: 'ok',
				complete: 100,
			} );

			render( <Database />, { wrapper: createWrapper() } );

			await jest.advanceTimersByTimeAsync( 1000 );

			expect( mockMutateAsync ).not.toHaveBeenCalled();
		} );
	} );

	describe( 'Error States', () => {
		it( 'should display DatabaseError component when result is "error"', () => {
			mockDatabaseState( {
				status: 'need-install',
				result: 'error',
				complete: 50,
				reason: 'Table creation failed',
				current: '5.4.0',
				next: '5.5.0',
				debug: [ 'SQL Error: Permission denied' ],
			} );

			render( <Database />, { wrapper: createWrapper() } );

			expect( screen.getByTestId( 'database-error' ) ).toBeInTheDocument();
			expect( screen.getByText( 'Table creation failed' ) ).toBeInTheDocument();
		} );

		it( 'should display DatabaseApiError component when result is "failed"', () => {
			mockDatabaseState( {
				status: 'need-update',
				result: 'failed',
				complete: 30,
				reason: 'API connection failed',
			} );

			render( <Database />, { wrapper: createWrapper() } );

			expect( screen.getByTestId( 'api-error' ) ).toBeInTheDocument();
		} );

		it( 'should not show spinner when error occurs', () => {
			mockDatabaseState( {
				status: 'need-install',
				result: 'error',
				complete: 50,
				reason: 'Error occurred',
			} );

			render( <Database />, { wrapper: createWrapper() } );

			expect( screen.queryByTestId( 'spinner' ) ).not.toBeInTheDocument();
		} );

		it( 'should not trigger upgrade when error occurs', async () => {
			mockDatabaseState( {
				status: 'need-install',
				result: 'error',
				complete: 50,
				reason: 'Error occurred',
			} );

			render( <Database />, { wrapper: createWrapper() } );

			await jest.advanceTimersByTimeAsync( 1000 );

			expect( mockMutateAsync ).not.toHaveBeenCalled();
		} );

		it( 'should call retry mutation with "retry" when retry is clicked', () => {
			mockDatabaseState( {
				status: 'need-install',
				result: 'error',
				complete: 50,
				reason: 'Error occurred',
			} );

			render( <Database />, { wrapper: createWrapper() } );

			const retryButton = screen.getByText( 'Retry' );
			retryButton.click();

			expect( mockMutate ).toHaveBeenCalledWith( 'retry' );
		} );
	} );

	describe( 'PreventLeaveWarning', () => {
		it( 'should show prevent leave warning when in progress', () => {
			mockDatabaseState( {
				status: 'need-install',
				result: 'ok',
				complete: 50,
			} );

			render( <Database />, { wrapper: createWrapper() } );

			expect( screen.getByTestId( 'prevent-leave' ) ).toBeInTheDocument();
			expect(
				screen.getByText( 'Leaving before the process has completed may cause problems.' )
			).toBeInTheDocument();
		} );

		it( 'should not show prevent leave warning on error', () => {
			mockDatabaseState( {
				status: 'need-install',
				result: 'error',
				complete: 50,
				reason: 'Error',
			} );

			render( <Database />, { wrapper: createWrapper() } );

			expect( screen.queryByTestId( 'prevent-leave' ) ).not.toBeInTheDocument();
		} );

		it( 'should not show prevent leave warning on failed', () => {
			mockDatabaseState( {
				status: 'need-install',
				result: 'failed',
				complete: 50,
				reason: 'Failed',
			} );

			render( <Database />, { wrapper: createWrapper() } );

			expect( screen.queryByTestId( 'prevent-leave' ) ).not.toBeInTheDocument();
		} );
	} );

	describe( 'Edge Cases', () => {
		it( 'should handle 0% progress', () => {
			mockDatabaseState( {
				status: 'need-install',
				result: 'ok',
				complete: 0,
			} );

			render( <Database />, { wrapper: createWrapper() } );

			expect( screen.getByText( 'Progress: 0%' ) ).toBeInTheDocument();
		} );

		it( 'should handle 100% progress', () => {
			mockDatabaseState( {
				status: 'need-install',
				result: 'ok',
				complete: 100,
			} );

			render( <Database />, { wrapper: createWrapper() } );

			expect( screen.getByText( 'Progress: 100%' ) ).toBeInTheDocument();
		} );

		it( 'should display reminder message', () => {
			mockDatabaseState( {
				status: 'need-install',
				result: 'ok',
				complete: 25,
			} );

			render( <Database />, { wrapper: createWrapper() } );

			expect( screen.getByText( 'Please remain on this page until complete.' ) ).toBeInTheDocument();
		} );

		it( 'should not render children when not finished', () => {
			mockDatabaseState( {
				status: 'need-install',
				result: 'ok',
				complete: 50,
			} );

			render(
				<Database>
					<div data-testid="child-content">Should not appear</div>
				</Database>,
				{ wrapper: createWrapper() }
			);

			expect( screen.queryByTestId( 'child-content' ) ).not.toBeInTheDocument();
		} );
	} );

	describe( 'Polling loop', () => {
		it( 'should keep polling even when the server reports the same progress twice in a row', async () => {
			// Regression test: the poll used to be re-armed only when complete/status/result
			// actually changed value. If the server ever echoed back the same progress twice,
			// nothing in the effect's dependency array changed, so it silently stopped polling
			// forever with no error and no further requests.
			mockDatabaseState( {
				status: 'need-install',
				result: 'ok',
				complete: 50,
			} );

			render( <Database />, { wrapper: createWrapper() } );

			await jest.advanceTimersByTimeAsync( 1000 );
			expect( mockMutateAsync ).toHaveBeenCalledTimes( 1 );

			await jest.advanceTimersByTimeAsync( 1000 );
			expect( mockMutateAsync ).toHaveBeenCalledTimes( 2 );

			await jest.advanceTimersByTimeAsync( 1000 );
			expect( mockMutateAsync ).toHaveBeenCalledTimes( 3 );
		} );

		it( 'should report a stuck error after sustained no-progress instead of polling forever', async () => {
			mockDatabaseState( {
				status: 'need-install',
				result: 'ok',
				complete: 50,
			} );

			render( <Database />, { wrapper: createWrapper() } );

			// 30 consecutive no-progress polls trip the stuck detector (STUCK_THRESHOLD).
			await jest.advanceTimersByTimeAsync( 1000 * 31 );

			expect( mockSetDatabase ).toHaveBeenCalledWith(
				expect.objectContaining( {
					result: 'error',
					reason: expect.stringContaining( 'stuck' ),
				} )
			);

			// It should have polled up to the threshold, then stopped rather than continuing.
			expect( mockMutateAsync ).toHaveBeenCalledTimes( 30 );

			const callsAtDetection = mockMutateAsync.mock.calls.length;
			await jest.advanceTimersByTimeAsync( 1000 * 3 );
			expect( mockMutateAsync ).toHaveBeenCalledTimes( callsAtDetection );
		} );

		it( 'should not keep polling after the component unmounts', async () => {
			mockDatabaseState( {
				status: 'need-install',
				result: 'ok',
				complete: 50,
			} );

			const { unmount } = render( <Database />, { wrapper: createWrapper() } );

			await jest.advanceTimersByTimeAsync( 1000 );
			expect( mockMutateAsync ).toHaveBeenCalledTimes( 1 );

			unmount();

			await jest.advanceTimersByTimeAsync( 1000 * 3 );
			expect( mockMutateAsync ).toHaveBeenCalledTimes( 1 );
		} );
	} );
} );
