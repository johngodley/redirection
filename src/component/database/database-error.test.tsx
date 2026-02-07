import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import DatabaseError from './database-error';
import * as stores from 'stores';
import * as useSettings from 'lib/api/hooks/use-settings';
import type { DatabaseState } from 'stores/settings-store';

// Mock dependencies using path aliases that match the component imports
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
jest.mock( 'react-textarea-autosize', () => ( { value, readOnly, rows }: any ) => (
	<textarea data-testid="error-details" value={ value } readOnly={ readOnly } rows={ rows } />
) );

const mockUseSettingsStore = stores.useSettingsStore as jest.MockedFunction< typeof stores.useSettingsStore >;
const mockUseDatabaseUpgrade = useSettings.useDatabaseUpgrade as jest.MockedFunction<
	typeof useSettings.useDatabaseUpgrade
>;

// Mock global Redirectioni10n
( global as any ).window.Redirectioni10n = {
	versions: 'WordPress 6.4, PHP 8.2, Redirection 5.5.0',
	pluginRoot: '/wp-admin/admin.php?page=redirection',
};

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

// Helper to mock the settings store with database state
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
	};
	mockUseSettingsStore.mockImplementation( ( selector: any ) => {
		return selector ? selector( state ) : state;
	} );
}

describe( 'DatabaseError Component', () => {
	const mockOnRetry = jest.fn();
	const mockMutate = jest.fn();

	beforeEach( () => {
		jest.clearAllMocks();

		mockUseDatabaseUpgrade.mockReturnValue( {
			mutate: mockMutate,
		} as any );

		// Set default mock state
		mockDatabaseState( {
			current: '5.4.0',
			next: '5.5.0',
			debug: [ 'SQL Error: Table not found', 'Permission denied on wp_redirection_items' ],
			reason: 'Database migration failed',
			inProgress: false,
			result: 'error',
			status: 'need-update',
			complete: 50,
		} );
	} );

	it( 'should render error title', () => {
		render( <DatabaseError error="Test error message" onRetry={ mockOnRetry } />, {
			wrapper: createWrapper(),
		} );

		expect( screen.getByText( 'Database problem' ) ).toBeInTheDocument();
	} );

	it( 'should display the error message', () => {
		render( <DatabaseError error="Connection timeout occurred" onRetry={ mockOnRetry } />, {
			wrapper: createWrapper(),
		} );

		expect( screen.getByText( 'Connection timeout occurred' ) ).toBeInTheDocument();
	} );

	it( 'should call onRetry when "Try again" is clicked', () => {
		render( <DatabaseError error="Test error" onRetry={ mockOnRetry } />, {
			wrapper: createWrapper(),
		} );

		const tryAgainButton = screen.getByText( 'Try again' );
		fireEvent.click( tryAgainButton );

		expect( mockOnRetry ).toHaveBeenCalledTimes( 1 );
	} );

	it( 'should display "Skip this stage" button when current version is set', () => {
		render( <DatabaseError error="Test error" onRetry={ mockOnRetry } />, {
			wrapper: createWrapper(),
		} );

		expect( screen.getByText( 'Skip this stage' ) ).toBeInTheDocument();
	} );

	it( 'should call upgrade mutation with "skip" when skip button is clicked', () => {
		render( <DatabaseError error="Test error" onRetry={ mockOnRetry } />, {
			wrapper: createWrapper(),
		} );

		const skipButton = screen.getByText( 'Skip this stage' );
		fireEvent.click( skipButton );

		expect( mockMutate ).toHaveBeenCalledWith( 'skip' );
	} );

	it( 'should display "Stop upgrade" button when current version is set', () => {
		render( <DatabaseError error="Test error" onRetry={ mockOnRetry } />, {
			wrapper: createWrapper(),
		} );

		expect( screen.getByText( 'Stop upgrade' ) ).toBeInTheDocument();
	} );

	it( 'should call upgrade mutation with "stop" when stop button is clicked', () => {
		render( <DatabaseError error="Test error" onRetry={ mockOnRetry } />, {
			wrapper: createWrapper(),
		} );

		const stopButton = screen.getByText( 'Stop upgrade' );
		fireEvent.click( stopButton );

		expect( mockMutate ).toHaveBeenCalledWith( 'stop' );
	} );

	it( 'should not display skip/stop buttons when current version is "-"', () => {
		mockDatabaseState( {
			current: '-',
			next: '5.5.0',
			debug: [],
			reason: 'Initial setup error',
			inProgress: false,
			result: 'error',
			status: 'need-install',
			complete: 0,
		} );

		render( <DatabaseError error="Test error" onRetry={ mockOnRetry } />, {
			wrapper: createWrapper(),
		} );

		expect( screen.queryByText( 'Skip this stage' ) ).not.toBeInTheDocument();
		expect( screen.queryByText( 'Stop upgrade' ) ).not.toBeInTheDocument();
	} );

	it( 'should display error recovery details in textarea', () => {
		render( <DatabaseError error="Migration failed" onRetry={ mockOnRetry } />, {
			wrapper: createWrapper(),
		} );

		const textarea = screen.getByTestId( 'error-details' ) as HTMLTextAreaElement;

		expect( textarea.value ).toContain( 'Message: Database migration failed' );
		expect( textarea.value ).toContain( 'Installed: 5.4.0' );
		expect( textarea.value ).toContain( 'Next: 5.5.0' );
		expect( textarea.value ).toContain( 'Debug: SQL Error: Table not found' );
		expect( textarea.value ).toContain( 'Permission denied on wp_redirection_items' );
	} );

	it( 'should format error details without debug when debug array is empty', () => {
		mockDatabaseState( {
			current: '5.4.0',
			next: '5.5.0',
			debug: [],
			reason: 'Simple error',
			inProgress: false,
			result: 'error',
			status: 'need-update',
			complete: 50,
		} );

		render( <DatabaseError error="Test error" onRetry={ mockOnRetry } />, {
			wrapper: createWrapper(),
		} );

		const textarea = screen.getByTestId( 'error-details' ) as HTMLTextAreaElement;

		expect( textarea.value ).toContain( 'Message: Simple error' );
		expect( textarea.value ).toContain( 'Installed: 5.4.0' );
		expect( textarea.value ).toContain( 'Next: 5.5.0' );
		expect( textarea.value ).not.toContain( 'Debug:' );
	} );

	it( 'should format error details without reason when reason is empty', () => {
		mockDatabaseState( {
			current: '5.4.0',
			next: '5.5.0',
			debug: [ 'Debug info' ],
			reason: '',
			inProgress: false,
			result: 'error',
			status: 'need-update',
			complete: 50,
		} );

		render( <DatabaseError error="Test error" onRetry={ mockOnRetry } />, {
			wrapper: createWrapper(),
		} );

		const textarea = screen.getByTestId( 'error-details' ) as HTMLTextAreaElement;

		expect( textarea.value ).not.toContain( 'Message:' );
		expect( textarea.value ).toContain( 'Installed: 5.4.0' );
		expect( textarea.value ).toContain( 'Next: 5.5.0' );
		expect( textarea.value ).toContain( 'Debug: Debug info' );
	} );

	it( 'should render support button', () => {
		render( <DatabaseError error="Test error" onRetry={ mockOnRetry } />, {
			wrapper: createWrapper(),
		} );

		const supportButton = screen.getByText( 'ask for support' );
		expect( supportButton ).toBeInTheDocument();
		expect( supportButton.tagName ).toBe( 'BUTTON' );
	} );

	it( 'should render textarea as readonly', () => {
		render( <DatabaseError error="Test error" onRetry={ mockOnRetry } />, {
			wrapper: createWrapper(),
		} );

		const textarea = screen.getByTestId( 'error-details' ) as HTMLTextAreaElement;
		expect( textarea ).toHaveAttribute( 'readonly' );
	} );

	it( 'should render textarea with 15 rows', () => {
		render( <DatabaseError error="Test error" onRetry={ mockOnRetry } />, {
			wrapper: createWrapper(),
		} );

		const textarea = screen.getByTestId( 'error-details' );
		expect( textarea ).toHaveAttribute( 'rows', '15' );
	} );

	it( 'should display error class on container', () => {
		const { container } = render( <DatabaseError error="Test error" onRetry={ mockOnRetry } />, {
			wrapper: createWrapper(),
		} );

		expect( container.querySelector( '.redirection-database_error' ) ).toBeInTheDocument();
		expect( container.querySelector( '.wpl-error' ) ).toBeInTheDocument();
	} );
} );
