import { act, renderHook, waitFor } from '@testing-library/react';
import { useGroupDropdown, useImporterList, useImportRunner } from 'lib/api/hooks';
import { isJsonFile, sniffImportFile } from 'component/import-export/import-sniff';
import useImportPage from './use-import-page';
import type { ImportMutationVariables } from 'lib/api/hooks';

jest.mock( 'lib/api/hooks', () => ( {
	...jest.requireActual( 'lib/api/hooks' ),
	useGroupDropdown: jest.fn(),
	useImporterList: jest.fn(),
	useImportRunner: jest.fn(),
} ) );

jest.mock( 'component/import-export/import-sniff', () => ( {
	...jest.requireActual( 'component/import-export/import-sniff' ),
	isJsonFile: jest.fn(),
	sniffImportFile: jest.fn(),
} ) );

const mockUseGroupDropdown = useGroupDropdown as jest.MockedFunction< typeof useGroupDropdown >;
const mockUseImporterList = useImporterList as jest.MockedFunction< typeof useImporterList >;
const mockUseImportRunner = useImportRunner as jest.MockedFunction< typeof useImportRunner >;
const mockIsJsonFile = isJsonFile as jest.MockedFunction< typeof isJsonFile >;
const mockSniffImportFile = sniffImportFile as jest.MockedFunction< typeof sniffImportFile >;

function getDeleteSourceEvent() {
	const input = document.createElement( 'input' );
	input.name = 'delete_source';
	input.checked = true;

	return {
		currentTarget: input,
	} as React.ChangeEvent< HTMLInputElement >;
}

describe( 'useImportPage', () => {
	const mutate = jest.fn();
	const reset = jest.fn();
	const plugin = {
		id: 'wordpress',
		name: 'WordPress permalink redirect',
		description: 'Import redirects created by WordPress permalink redirects.',
		preview_supported: true,
		total: 4,
	};
	const wordpressOldSlugsPlugin = {
		id: 'wordpress-old-slugs',
		name: 'WordPress permalink redirect',
		description: 'Import redirects created by WordPress permalink redirects.',
		preview_supported: true,
		total: 4,
	};
	const safeRedirectManagerPlugin = {
		id: 'safe-redirect-manager',
		name: 'Safe Redirect Manager',
		description: 'Import redirects created by Safe Redirect Manager.',
		preview_supported: true,
		total: 4,
	};
	let runnerOptions:
		| {
				onSuccess?: ( data: any, variables: ImportMutationVariables ) => void;
		  }
		| undefined;
	let confirmSpy: jest.SpiedFunction< typeof window.confirm >;
	let refetchGroups: jest.Mock;

	beforeEach( () => {
		jest.clearAllMocks();
		runnerOptions = undefined;
		refetchGroups = jest.fn();
		confirmSpy = jest.spyOn( window, 'confirm' ).mockImplementation( () => true );

		mockUseGroupDropdown.mockReturnValue( {
			data: {
				items: [
					{ id: 11, name: 'Imported redirects' },
					{ id: 12, name: 'Secondary group' },
				],
			},
			isSuccess: true,
			refetch: refetchGroups,
		} as any );

		mockUseImporterList.mockReturnValue( {
			data: [ plugin, wordpressOldSlugsPlugin, safeRedirectManagerPlugin ],
			isLoading: false,
		} as any );

		mockUseImportRunner.mockImplementation( ( options ) => {
			runnerOptions = options as typeof runnerOptions;

			return {
				mutate,
				reset,
				isPending: false,
				isSuccess: false,
			} as any;
		} );
	} );

	afterEach( () => {
		jest.restoreAllMocks();
	} );

	it( 'clears preview state and restores the default group when switching from a JSON file to a plugin importer', async () => {
		const file = new File( [ '{}' ], 'redirects.json', { type: 'application/json' } );

		mockIsJsonFile.mockReturnValue( true );
		mockSniffImportFile.mockResolvedValue( {
			format: 'json',
			valid: true,
			version: '5.8.0',
			groups: 1,
			redirects: 2,
		} );

		const { result } = renderHook( () => useImportPage() );

		act( () => {
			result.current.onFileInputChange( {
				target: { files: [ file ] },
			} as any );
		} );

		await waitFor( () => expect( result.current.state.fileInfo ).not.toBeNull() );

		expect( result.current.state.activeImportType ).toBe( 'file' );
		expect( result.current.state.group ).toBe( 0 );
		expect( result.current.state.hasGroups ).toBe( true );

		act( () => {
			runnerOptions?.onSuccess?.(
				{
					created: 0,
					updated: 1,
					ignored: 0,
					groups_created: 0,
					preview: [],
				},
				{
					sourceType: 'file',
					mode: 'preview',
					file,
					groupId: 0,
					duplicateMode: 'update',
				}
			);
		} );

		expect( result.current.state.lastImport ).not.toBe( false );
		expect( result.current.state.lastImportWasDryRun ).toBe( true );

		act( () => {
			result.current.onSelectPlugin( plugin );
		} );

		expect( result.current.state.activeImportType ).toBe( 'plugin' );
		expect( result.current.state.activePluginId ).toBe( plugin.id );
		expect( result.current.state.group ).toBe( 11 );
		expect( result.current.state.lastImport ).toBe( false );
		expect( result.current.state.lastImportWasDryRun ).toBeNull();
	} );

	it( 'clears preview state and deselects the plugin when switching back to a file importer', async () => {
		const file = new File( [ 'source,target\n/one,/two' ], 'redirects.csv', { type: 'text/csv' } );

		mockIsJsonFile.mockReturnValue( false );
		mockSniffImportFile.mockResolvedValue( {
			format: 'csv',
			valid: true,
			separator: ',',
			columns: 2,
			rows: 2,
		} );

		const { result } = renderHook( () => useImportPage() );

		act( () => {
			result.current.onSelectPlugin( plugin );
		} );

		act( () => {
			runnerOptions?.onSuccess?.(
				{
					created: 2,
					updated: 0,
					ignored: 0,
					groups_created: 0,
					preview: [],
				},
				{
					sourceType: 'plugin',
					mode: 'preview',
					pluginId: plugin.id,
					groupId: 11,
					duplicateMode: 'import',
				}
			);
		} );

		expect( result.current.state.activePluginId ).toBe( plugin.id );
		expect( result.current.state.lastImport ).not.toBe( false );

		act( () => {
			result.current.onFileInputChange( {
				target: { files: [ file ] },
			} as any );
		} );

		await waitFor( () => expect( result.current.state.fileInfo ).not.toBeNull() );

		expect( result.current.state.activeImportType ).toBe( 'file' );
		expect( result.current.state.activePluginId ).toBeNull();
		expect( result.current.state.group ).toBe( 11 );
		expect( result.current.state.lastImport ).toBe( false );
		expect( result.current.state.lastImportWasDryRun ).toBeNull();
	} );

	it( 'allows the active plugin importer to be toggled off', () => {
		const { result } = renderHook( () => useImportPage() );

		act( () => {
			result.current.onSelectPlugin( plugin );
		} );

		expect( result.current.state.activeImportType ).toBe( 'plugin' );
		expect( result.current.state.activePluginId ).toBe( plugin.id );

		act( () => {
			result.current.onSelectPlugin( plugin );
		} );

		expect( result.current.state.activeImportType ).toBeNull();
		expect( result.current.state.activePluginId ).toBeNull();
	} );

	it( 'refetches groups once when the initial group list is empty', async () => {
		mockUseGroupDropdown.mockReturnValue( {
			data: {
				items: [],
			},
			isSuccess: true,
			refetch: refetchGroups,
		} as any );

		const { result, rerender } = renderHook( () => useImportPage() );

		await waitFor( () => expect( refetchGroups ).toHaveBeenCalledTimes( 1 ) );
		expect( result.current.state.hasGroups ).toBe( false );

		rerender();

		expect( refetchGroups ).toHaveBeenCalledTimes( 1 );
	} );

	it( 'passes delete original data for the WordPress permalink importer', () => {
		const { result } = renderHook( () => useImportPage() );

		act( () => {
			result.current.onSelectPlugin( {
				...wordpressOldSlugsPlugin,
			} );
		} );

		act( () => {
			result.current.onOptionsChange( getDeleteSourceEvent() );
		} );

		act( () => {
			result.current.onImport( true );
		} );

		expect( mutate ).toHaveBeenCalledWith(
			expect.objectContaining( {
				sourceType: 'plugin',
				mode: 'preview',
				pluginId: 'wordpress-old-slugs',
				deleteSource: true,
			} )
		);
	} );

	it( 'passes delete original data for the Safe Redirect Manager importer', () => {
		const { result } = renderHook( () => useImportPage() );

		act( () => {
			result.current.onSelectPlugin( {
				...safeRedirectManagerPlugin,
			} );
		} );

		act( () => {
			result.current.onOptionsChange( getDeleteSourceEvent() );
		} );

		act( () => {
			result.current.onImport( true );
		} );

		expect( mutate ).toHaveBeenCalledWith(
			expect.objectContaining( {
				sourceType: 'plugin',
				mode: 'preview',
				pluginId: 'safe-redirect-manager',
				deleteSource: true,
			} )
		);
	} );

	it( 'treats pasted JSON content as a paste importer and defaults the group to use groups in the file', () => {
		const { result } = renderHook( () => useImportPage() );

		act( () => {
			result.current.onPasteTextChange(
				JSON.stringify( {
					plugin: { version: '5.8.0' },
					groups: [ { id: 1, name: 'Imported' } ],
					redirects: [ { id: 2, url: '/source' } ],
				} )
			);
		} );

		expect( result.current.state.activeImportType ).toBe( 'paste' );
		expect( result.current.state.group ).toBe( 0 );
		expect( result.current.state.pasteFile?.name ).toBe( 'pasted-import.json' );
		expect( result.current.state.pasteInfo ).toEqual(
			expect.objectContaining( {
				format: 'json',
				valid: true,
			} )
		);
		expect( result.current.state.selectedSections ).toEqual( [ 'groups', 'redirects' ] );
	} );

	it( 'submits pasted CSV content through the file import path', () => {
		const { result } = renderHook( () => useImportPage() );

		act( () => {
			result.current.onPasteTextChange( 'source,target\n/one,/two' );
		} );

		act( () => {
			result.current.onImport( true );
		} );

		expect( mutate ).toHaveBeenCalledWith(
			expect.objectContaining( {
				sourceType: 'file',
				mode: 'preview',
				groupId: 11,
				file: expect.objectContaining( {
					name: 'pasted-import.csv',
				} ),
			} )
		);
	} );

	it( 'submits pasted Apache content through the file import path using a .htaccess filename', () => {
		const { result } = renderHook( () => useImportPage() );

		act( () => {
			result.current.onPasteTextChange( 'RewriteRule ^old-path$ /new-path [R=301,L]' );
		} );

		act( () => {
			result.current.onImport( true );
		} );

		expect( mutate ).toHaveBeenCalledWith(
			expect.objectContaining( {
				sourceType: 'file',
				mode: 'preview',
				groupId: 11,
				file: expect.objectContaining( {
					name: 'pasted-import.htaccess',
				} ),
			} )
		);
	} );

	it( 'shows a destructive confirm before importing when delete original data is enabled', () => {
		const { result } = renderHook( () => useImportPage() );

		act( () => {
			result.current.onSelectPlugin( {
				...wordpressOldSlugsPlugin,
			} );
		} );

		act( () => {
			result.current.onOptionsChange( getDeleteSourceEvent() );
		} );

		act( () => {
			result.current.onImport( false );
		} );

		expect( confirmSpy ).toHaveBeenCalledWith(
			'This will import the redirects and delete the original data. Are you sure?'
		);
		expect( mutate ).toHaveBeenCalledWith(
			expect.objectContaining( {
				sourceType: 'plugin',
				mode: 'import',
				pluginId: 'wordpress-old-slugs',
				deleteSource: true,
			} )
		);
	} );

	it( 'shows a destructive confirm before importing Safe Redirect Manager when delete original data is enabled', () => {
		const { result } = renderHook( () => useImportPage() );

		act( () => {
			result.current.onSelectPlugin( {
				...safeRedirectManagerPlugin,
			} );
		} );

		act( () => {
			result.current.onOptionsChange( getDeleteSourceEvent() );
		} );

		act( () => {
			result.current.onImport( false );
		} );

		expect( confirmSpy ).toHaveBeenCalledWith(
			'This will import the redirects and delete the original data. Are you sure?'
		);
		expect( mutate ).toHaveBeenCalledWith(
			expect.objectContaining( {
				sourceType: 'plugin',
				mode: 'import',
				pluginId: 'safe-redirect-manager',
				deleteSource: true,
			} )
		);
	} );
} );
