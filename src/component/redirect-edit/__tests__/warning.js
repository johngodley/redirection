/**
 * Internal dependencies
 */

import {
	isRegex,
	getWarningFromState,
} from '../warning';

describe( 'redirect warnings', () => {
	describe( 'isRegex', () => {
		test( 'plain string returns false', () => {
			expect( isRegex( 'cat' ) ).toBe( false );
		} );

		test( '.? returns true', () => {
			expect( isRegex( 'something .? here' ) ).toBe( true );
		} );

		test( '? returns false', () => {
			expect( isRegex( 'something ? here' ) ).toBe( false );
		} );

		test( 'regex characters return true', () => {
			expect( isRegex( 'test *' ) ).toBe( true );
			expect( isRegex( 'test \\' ) ).toBe( true );
			expect( isRegex( 'test (' ) ).toBe( true );
			expect( isRegex( 'test )' ) ).toBe( true );
			expect( isRegex( 'test ^' ) ).toBe( true );
			expect( isRegex( 'test $' ) ).toBe( true );
		} );
	} );

	describe( 'detect warning', () => {
		test( 'no warning in plain URL', () => {
			const values = { url: '/url', flag_regex: false };
			expect( getWarningFromState( values ) ).toHaveLength( 0 );
		} );

		test( 'detect # in URL', () => {
			const values = { url: '/this#', flag_regex: false };
			expect( getWarningFromState( values ) ).toHaveLength( 1 );
		} );

		test( 'detect leading / in URL', () => {
			const values = { url: 'this', flag_regex: false };
			expect( getWarningFromState( values ) ).toHaveLength( 1 );
		} );

		// test( 'detect regex without flag in URL', () => {
		// 	const values = { url: 'this$', flag_regex: false };
		// 	expect( getWarningFromState( values ) ).toHaveLength( 1 );
		// } );

		test( 'detect greedy regex in URL', () => {
			const values = { url: '/this*', flag_regex: true };
			expect( getWarningFromState( values ) ).toHaveLength( 2 );
		} );

		// test( 'detect redirect everything', () => {
		// 	const values = { url: '/(.*)', flag_regex: true };
		// 	expect( getWarningFromState( values ) ).toHaveLength( 1 );
		// } );
	} );
} );
