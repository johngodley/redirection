import { getErrorMessage, getErrorCode } from "../api-error";

describe( 'API error', () => {
	describe( 'getErrorMessage', () => {
		test( 'json is 0 means admin ajax returned 0', () => {
			expect( getErrorMessage( 0 ) ).toBe( 'Admin AJAX returned 0' );
		} );

		test( 'json has a message', () => {
			expect( getErrorMessage( { message: 'cat' } ) ).toBe( 'cat' );
		} );

		test( 'unknown error message', () => {
			console.error = jest.fn();

			expect( getErrorMessage( { thing: 1 } ) ).toBe( 'Unknown error thing' );
			expect( console.error ).toHaveBeenCalledWith( { thing: 1 } );
		} );
	} );

	describe( 'getErrorCode', () => {
		test( 'json is 0 means admin ajax returned 0', () => {
			expect( getErrorCode( 0 ) ).toBe( 'admin-ajax' );
		} );

		test( 'json has a code', () => {
			expect( getErrorCode( { code: 'cat' } ) ).toBe( 'cat' );
		} );

		test( 'json has a error_code', () => {
			expect( getErrorCode( { error_code: 'cat' } ) ).toBe( 'cat' );
		} );

		test( 'json has a data.error_code', () => {
			expect( getErrorCode( { data: { error_code: 'cat' } } ) ).toBe( 'cat' );
		} );

		test( 'unknown error message', () => {
			expect( getErrorCode( { thing: 1 } ) ).toBe( 'unknown' );
		} );
	} );
} );
