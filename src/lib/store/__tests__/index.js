/**
 * Internal dependencies
 */

import {
	setItem,
	restoreToOriginal,
	setRows,
	setTable,
	setTotal,
	setSaving,
	removeSaving,
} from '../../lib/store';

describe( 'Store Reducer', () => {
	test( 'setTable returns existing table if non provided', () => {
		expect( setTable( { table: 1 }, {} ) ).toEqual( 1 );
	} );

	test( 'setTable returns new table with extra details', () => {
		expect( setTable( { table: { test: 1, cat: 2 } }, { table: { cat: 3 } } ) ).toEqual( { test: 1, cat: 3 } );
	} );

	test( 'setTotal returns existing total if non provided', () => {
		expect( setTotal( { total: 1 }, {} ) ).toEqual( 1 );
	} );

	test( 'setTotal returns new total', () => {
		expect( setTotal( { total: 1 }, { total: 3 } ) ).toEqual( 3 );
	} );

	test( 'setSaving returns new value at end of existing', () => {
		expect( setSaving( { saving: [] }, { saving: [ 1 ] } ) ).toEqual( [ 1 ] );
	} );

	test( 'removeSaving returns existing values if value to remove isnt present', () => {
		expect( removeSaving( { saving: [ 1, 2, 3 ] }, { saving: [ 4 ] } ) ).toEqual( [ 1, 2, 3 ] );
	} );

	test( 'removeSaving returns new values with existing value removed', () => {
		expect( removeSaving( { saving: [ 1, 3 ] }, { saving: [ 2 ] } ) ).toEqual( [ 1, 3 ] );
	} );

	test( 'restoreToOriginal leaves rows intact if no item is present', () => {
		expect( restoreToOriginal( { rows: [ 1 ] }, { something: 2 } ) ).toEqual( [ 1 ] );
	} );

	test( 'restoreToOriginal restores original value if item is present', () => {
		expect( restoreToOriginal( { rows: [ { id: 1, original: { cat: 1 } } ] }, { item: { id: 1 } } ) ).toEqual( [ { cat: 1 } ] );
	} );

	test( 'setItem returns existing rows if no item is present', () => {
		expect( setItem( { rows: [ 1 ] }, { cat: 1 } ) ).toEqual( [ 1 ] );
	} );

	test( 'setItem returns new rows with item replaced and original set to old value', () => {
		expect( setItem( { rows: [ { id: 1, cat: true } ] }, { item: { id: 1, cat: false } } ) ).toEqual( [ { id: 1, cat: false, original: { id: 1, cat: true } } ] );
	} );

	test( 'setRows returns new rows with item replaced and original set to old value if only one item is provided', () => {
		expect( setRows( { rows: [ { id: 1, cat: true } ] }, { item: { id: 1, cat: false } } ) ).toEqual( [ { id: 1, cat: false, original: { id: 1, cat: true } } ] );
	} );

	test( 'setRows returns new rows if items are provided', () => {
		expect( setRows( { rows: [ { id: 1, cat: true } ] }, { items: [ { id: 1, cat: false } ] } ) ).toEqual( [ { id: 1, cat: false } ] );
	} );
} );
