import { act, renderHook, waitFor } from '@testing-library/react';
import { useExport, useExportPreview, useGroupList } from 'lib/api/hooks';
import { useMessageStore } from 'stores';
import useExportPage from './use-export-page';

jest.mock( 'lib/api/hooks', () => ( {
	...jest.requireActual( 'lib/api/hooks' ),
	useExport: jest.fn(),
	useExportPreview: jest.fn(),
	useGroupList: jest.fn(),
} ) );
jest.mock( 'stores', () => ( {
	useMessageStore: jest.fn(),
} ) );

const mockUseExport = useExport as jest.MockedFunction< typeof useExport >;
const mockUseExportPreview = useExportPreview as jest.MockedFunction< typeof useExportPreview >;
const mockUseGroupList = useGroupList as jest.MockedFunction< typeof useGroupList >;
const mockUseMessageStore = useMessageStore as unknown as jest.Mock;

describe( 'useExportPage', () => {
	let previewTotal = 9;
	let previewError = false;

	beforeEach( () => {
		jest.clearAllMocks();
		previewTotal = 9;
		previewError = false;

		mockUseGroupList.mockReturnValue( {
			data: {
				items: [
					{ id: 11, name: 'Imported redirects', moduleName: 'WordPress' },
					{ id: 12, name: 'Apache group', moduleName: 'Apache' },
				],
			},
		} as any );

		mockUseMessageStore.mockReturnValue( {
			incrementProgress: jest.fn(),
			decrementProgress: jest.fn(),
			addNotice: jest.fn(),
			addError: jest.fn(),
		} );

		mockUseExport.mockReturnValue( {
			mutate: jest.fn(),
			isPending: false,
		} as any );

		mockUseExportPreview.mockImplementation( ( variables ) => {
			if ( variables.exportType === 'log' ) {
				return {
					data: { total: 5, estimatedSize: 512 },
					isLoading: false,
					isFetching: false,
					isError: false,
				} as any;
			}

			if ( variables.exportType === 'redirect' && variables.redirectScopeType === 'group' ) {
				return {
					data: { total: 1, estimatedSize: 128 },
					isLoading: false,
					isFetching: false,
					isError: false,
				} as any;
			}

			return {
				data: previewError ? undefined : { total: previewTotal, estimatedSize: 1024 },
				isLoading: false,
				isFetching: false,
				isError: previewError,
			} as any;
		} );
	} );

	it( 'loads redirect preview totals on mount', async () => {
		const { result } = renderHook( () => useExportPage() );

		await waitFor( () => expect( result.current.state.previewTotal ).toBe( 9 ) );
	} );

	it( 'refreshes preview totals when switching redirect scope to group', async () => {
		const { result } = renderHook( () => useExportPage() );

		await waitFor( () => expect( result.current.state.previewTotal ).toBe( 9 ) );

		act( () => {
			result.current.onChange( 'redirectScopeType', 'group' );
		} );

		await waitFor( () => expect( result.current.state.previewTotal ).toBe( 1 ) );
		expect( result.current.state.redirectGroup ).toBe( 11 );
	} );

	it( 'switches logs to JSON support and refreshes preview totals', async () => {
		const { result } = renderHook( () => useExportPage() );

		act( () => {
			result.current.onSelectType( 'log' );
		} );

		await waitFor( () => expect( result.current.state.previewTotal ).toBe( 5 ) );

		expect( result.current.availableFormats ).toEqual( [ 'json', 'csv' ] );
		expect( result.current.state.format ).toBe( 'json' );
	} );
} );
