import { act, renderHook, waitFor } from '@testing-library/react';
import { useExport, useExportPreview, useGroupList } from 'lib/api/hooks';
import useExportPage from './use-export-page';

jest.mock( 'lib/api/hooks', () => ( {
	...jest.requireActual( 'lib/api/hooks' ),
	useExport: jest.fn(),
	useExportPreview: jest.fn(),
	useGroupList: jest.fn(),
} ) );
const mockUseExport = useExport as jest.MockedFunction< typeof useExport >;
const mockUseExportPreview = useExportPreview as jest.MockedFunction< typeof useExportPreview >;
const mockUseGroupList = useGroupList as jest.MockedFunction< typeof useGroupList >;

describe( 'useExportPage', () => {
	let previewTotal = 9;
	let previewError = false;
	let previewRefetch: jest.Mock;
	let exportReset: jest.Mock;

	beforeEach( () => {
		jest.clearAllMocks();
		previewTotal = 9;
		previewError = false;
		previewRefetch = jest.fn();
		exportReset = jest.fn();

		mockUseGroupList.mockReturnValue( {
			data: {
				items: [
					{ id: 11, name: 'Imported redirects', moduleName: 'WordPress' },
					{ id: 12, name: 'Apache group', moduleName: 'Apache' },
				],
			},
		} as any );

		mockUseExport.mockReturnValue( {
			mutate: jest.fn(),
			reset: exportReset,
			isPending: false,
		} as any );

		mockUseExportPreview.mockImplementation( ( variables ) => {
			if ( variables.exportType === 'log' ) {
				return {
					data: { total: 5, estimatedSize: 512 },
					isLoading: false,
					isFetching: false,
					isError: false,
					refetch: previewRefetch,
				} as any;
			}

			if ( variables.exportType === 'redirect' && variables.redirectScopeType === 'group' ) {
				return {
					data: { total: 1, estimatedSize: 128 },
					isLoading: false,
					isFetching: false,
					isError: false,
					refetch: previewRefetch,
				} as any;
			}

			return {
				data: previewError ? undefined : { total: previewTotal, estimatedSize: 1024 },
				isLoading: false,
				isFetching: false,
				isError: previewError,
				refetch: previewRefetch,
			} as any;
		} );
	} );

	it( 'loads redirect preview totals on mount', async () => {
		const { result } = renderHook( () => useExportPage() );

		expect( result.current.state.previewTotal ).toBeNull();

		act( () => {
			result.current.onToggleType( 'redirect' );
		} );

		await waitFor( () => expect( result.current.state.previewTotal ).toBe( 9 ) );
		expect( previewRefetch ).toHaveBeenCalled();
	} );

	it( 'refreshes preview totals when switching redirect scope to group', async () => {
		const { result } = renderHook( () => useExportPage() );

		act( () => {
			result.current.onToggleType( 'redirect' );
		} );

		await waitFor( () => expect( result.current.state.previewTotal ).toBe( 9 ) );

		act( () => {
			result.current.onChange( 'redirectScopeType', 'group' );
		} );

		await waitFor( () => expect( result.current.state.previewTotal ).toBe( 1 ) );
		expect( previewRefetch ).toHaveBeenCalled();
		expect( result.current.state.redirectGroup ).toBe( 11 );
	} );

	it( 'switches logs to combined JSON support and refreshes preview totals', async () => {
		const { result } = renderHook( () => useExportPage() );

		act( () => {
			result.current.onToggleType( 'log' );
		} );

		await waitFor( () => expect( result.current.state.previewTotal ).toBe( 5 ) );

		expect( previewRefetch ).toHaveBeenCalled();
		expect( result.current.availableFormats ).toEqual( [ 'json', 'csv' ] );
		expect( result.current.state.selectedTypes ).toEqual( [ 'log' ] );
		expect( result.current.state.format ).toBe( 'json' );
	} );

	it( 'refetches preview data when the export format changes', async () => {
		const { result } = renderHook( () => useExportPage() );

		act( () => {
			result.current.onToggleType( 'redirect' );
		} );

		await waitFor( () => expect( result.current.state.previewTotal ).toBe( 9 ) );
		previewRefetch.mockClear();

		act( () => {
			result.current.onChange( 'format', 'csv' );
		} );

		expect( result.current.state.format ).toBe( 'csv' );
		expect( previewRefetch ).toHaveBeenCalled();
	} );

	it( 'allows all export types to be deselected', async () => {
		const { result } = renderHook( () => useExportPage() );

		act( () => {
			result.current.onToggleType( 'redirect' );
		} );

		await waitFor( () => expect( result.current.state.previewTotal ).toBe( 9 ) );

		act( () => {
			result.current.onToggleType( 'redirect' );
		} );

		expect( result.current.state.selectedTypes ).toEqual( [] );
		expect( result.current.availableFormats ).toEqual( [] );
		expect( result.current.state.previewTotal ).toBeNull();
	} );
} );
