import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';
import DatabaseApiError from './api-error';

describe( 'DatabaseApiError Component', () => {
	const mockOnRetry = jest.fn();

	beforeEach( () => {
		jest.clearAllMocks();
	} );

	it( 'should render error title', () => {
		render( <DatabaseApiError onRetry={ mockOnRetry } /> );

		expect( screen.getByText( 'Database problem' ) ).toBeInTheDocument();
	} );

	it( 'should render "Try again" button', () => {
		render( <DatabaseApiError onRetry={ mockOnRetry } /> );

		expect( screen.getByText( 'Try again' ) ).toBeInTheDocument();
	} );

	it( 'should call onRetry when button is clicked', () => {
		render( <DatabaseApiError onRetry={ mockOnRetry } /> );

		const button = screen.getByText( 'Try again' );
		fireEvent.click( button );

		expect( mockOnRetry ).toHaveBeenCalledTimes( 1 );
	} );

	it( 'should render with error class', () => {
		const { container } = render( <DatabaseApiError onRetry={ mockOnRetry } /> );

		expect( container.querySelector( '.redirection-database_error' ) ).toBeInTheDocument();
		expect( container.querySelector( '.wpl-error' ) ).toBeInTheDocument();
	} );

	it( 'should render button as primary', () => {
		render( <DatabaseApiError onRetry={ mockOnRetry } /> );

		const button = screen.getByText( 'Try again' );
		expect( button ).toHaveClass( 'button-primary' );
	} );

	it( 'should accept error prop (even though not currently displayed)', () => {
		// The component accepts error prop but doesn't display it
		// This test ensures the prop is accepted without issues
		render( <DatabaseApiError error="API connection failed" onRetry={ mockOnRetry } /> );

		expect( screen.getByText( 'Database problem' ) ).toBeInTheDocument();
	} );

	it( 'should handle multiple clicks on retry button', () => {
		render( <DatabaseApiError onRetry={ mockOnRetry } /> );

		const button = screen.getByText( 'Try again' );
		fireEvent.click( button );
		fireEvent.click( button );
		fireEvent.click( button );

		expect( mockOnRetry ).toHaveBeenCalledTimes( 3 );
	} );
} );
