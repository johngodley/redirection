/**
 * Internal dependencies
 */

import {
	getPageUrl,
	getWordPressUrl,
	getPluginPage,
} from 'lib/wordpress-url';

describe( 'WordPress URL', () => {
	test( 'getPageUrl returns empty query when no params', () => {
		expect( getPageUrl( '' ) ).toEqual( {} );
	} );

	test( 'getPageUrl parses page query param', () => {
		expect( getPageUrl( '?some=thing&other=thing' ) ).toEqual( { some: 'thing', other: 'thing' } );
	} );

	test( 'getPluginPage returns redirect when no page param provided', () => {
		expect( getPluginPage( '?something' ) ).toEqual( 'redirect' );
	} );

	test( 'getPluginPage returns redirect when invalid page param provided', () => {
		expect( getPluginPage( '?page=cats' ) ).toEqual( 'redirect' );
	} );

	test( 'getPluginPage returns redirect when valid page param provided', () => {
		expect( getPluginPage( '?thing=here&sub=log' ) ).toEqual( 'log' );
		expect( getPluginPage( '?thing=here&sub=groups' ) ).toEqual( 'groups' );
		expect( getPluginPage( '?thing=here&sub=404s' ) ).toEqual( '404s' );
		expect( getPluginPage( '?thing=here&sub=io' ) ).toEqual( 'io' );
		expect( getPluginPage( '?thing=here&sub=options' ) ).toEqual( 'options' );
		expect( getPluginPage( '?thing=here&sub=support' ) ).toEqual( 'support' );
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

	test( 'getWordPressUrl ignores filterby when filter has no value', () => {
		expect( getWordPressUrl( { filterby: 'monkey' }, {}, '?existing=thing' ) ).toEqual( '?existing=thing' );
	} );
} );
