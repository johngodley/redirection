import { ZodError } from 'zod';
import { ErrorResponse } from 'types';

/**
 * Custom error class for API validation failures
 */
export class ApiValidationError extends Error {
	constructor(
		public zodError: ZodError,
		public response?: unknown
	) {
		super( 'API response validation failed' );
		this.name = 'ApiValidationError';
	}

	/**
	 * Get formatted validation errors
	 */
	getErrors() {
		return this.zodError.format();
	}

	/**
	 * Get first error message
	 */
	getFirstError(): string {
		const issues = this.zodError.issues;
		if ( issues.length > 0 && issues[ 0 ] ) {
			return issues[ 0 ].message;
		}
		return 'Validation error';
	}
}

/**
 * Handle API errors with type safety
 * @param error
 */
export function handleApiError( error: unknown ): Error {
	// Zod validation error
	if ( error instanceof ZodError ) {
		// Log validation details in development for easier debugging.
		// eslint-disable-next-line no-console
		console.error( 'API Validation Error:', error.format() );
		return new ApiValidationError( error );
	}

	// Already an Error instance
	if ( error instanceof Error ) {
		return error;
	}

	// Object with message
	if ( typeof error === 'object' && error !== null && 'message' in error ) {
		const err = error as ErrorResponse;
		return new Error( err.message || 'Unknown error' );
	}

	// Fallback
	return new Error( 'Unknown error occurred' );
}

/**
 * Check if error is a nonce error (needs refresh)
 * @param error
 */
export function isNonceError( error: unknown ): boolean {
	if ( typeof error === 'object' && error !== null && 'code' in error ) {
		const err = error as ErrorResponse;
		return err.code === 'rest_cookie_invalid_nonce';
	}
	return false;
}

/**
 * Extract error message from various error formats
 * @param error
 */
export function getErrorMessage( error: unknown ): string {
	if ( error instanceof ApiValidationError ) {
		return error.getFirstError();
	}

	if ( error instanceof Error ) {
		return error.message;
	}

	if ( typeof error === 'object' && error !== null && 'message' in error ) {
		const err = error as ErrorResponse;
		return err.message || 'An error occurred';
	}

	if ( typeof error === 'string' ) {
		return error;
	}

	return 'An unknown error occurred';
}
