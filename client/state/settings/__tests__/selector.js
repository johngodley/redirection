/**
 * Internal dependencies
 */

import {
	getOption,
	getFlags,
} from '../selector';

const FLAGS = {
	flag_regex: true,
	flag_query: 'all',
	flag_trailing: true,
	flag_case: true,
};

const STATE = {
	settings: {
		values: {
			test1: 'value1',
			test2: 'value2',
			test3: 'value3',

			... FLAGS,
		},
	},
};

describe( 'settings selector', () => {
	describe( 'getOption', () => {
		test( 'get all options', () => {
			expect( getOption( STATE ) ).toBe( STATE.settings.values );
		} );

		test( 'get existing option', () => {
			expect( getOption( STATE, 'test1' ) ).toBe( 'value1' );
		} );

		test( 'get non-existing option', () => {
			expect( getOption( STATE, 'test4' ) ).toBe( null );
		} );
	} );

	describe( 'getFlags', () => {
		test( 'get all options', () => {
			expect( getFlags( STATE ) ).toEqual( FLAGS );
		} );
	} );
} );
