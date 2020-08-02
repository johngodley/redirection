/**
 * Internal dependencies
 */

import {
	getPageUrl,
	getWordPressUrl,
} from '../index';

describe( 'WordPress URL', () => {
	test( 'getPageUrl returns empty query when no params', () => {
		expect( getPageUrl( '' ) ).toEqual( {} );
	} );

	test( 'getPageUrl parses page query param', () => {
		expect( getPageUrl( '?some=thing&other=thing' ) ).toEqual( { some: 'thing', other: 'thing' } );
	} );

	test( 'getWordPressUrl returns existing URL with no params', () => {
		expect( getWordPressUrl( {}, {}, '?existing=thing' ) ).toEqual( '?existing=thing' );
	} );

	test( 'getWordPressUrl returns existing URL with params', () => {
		expect( getWordPressUrl( { cat: 'monkey' }, {}, '?existing=thing' ) ).toEqual( '?existing=thing&cat=monkey' );
	} );

	test( 'getWordPressUrl returns existing URL with no params when defaulted', () => {
		expect( getWordPressUrl( { cat: 'monkey' }, { cat: 'monkey' }, '?existing=thing' ) ).toEqual( '?existing=thing' );
	} );
} );
