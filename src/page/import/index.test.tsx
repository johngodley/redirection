import '@testing-library/jest-dom';
import { render, screen } from '@testing-library/react';
import ImportPage from './index';
import useImportPage from './use-import-page';

jest.mock( 'component/import-export/style.scss', () => ( {} ) );
jest.mock( './use-import-page', () => jest.fn() );

const mockUseImportPage = useImportPage as jest.MockedFunction< typeof useImportPage >;

describe( 'ImportPage', () => {
	beforeEach( () => {
		mockUseImportPage.mockReturnValue( {
			state: {
				activeImportType: 'file',
				activePluginId: null,
				group: 0,
				hover: false,
				file: new File( [ '{}' ], 'redirects.json', { type: 'application/json' } ),
				pasteFile: false,
				pasteText: '',
				duplicateMode: 'import',
				deleteSource: false,
				fileInfo: {
					format: 'json',
					valid: true,
					contents: {
						redirects: 1,
						groups: 1,
					},
				},
				pasteInfo: null,
				isSniffing: false,
				currentError: null,
				selectedSections: [ 'redirects', 'groups' ],
				lastImport: false,
				lastImportWasDryRun: null,
				groupRows: [],
				hasGroups: false,
				importers: [],
				isLoadingImporters: false,
				isImporting: false,
				hasCompletedImport: false,
				hasActiveImport: true,
				previewSupported: true,
			},
			fileInputRef: { current: null },
			importingStatus: 'idle',
			onAddFileClick: jest.fn(),
			onCancel: jest.fn(),
			onClearFile: jest.fn(),
			onClearPaste: jest.fn(),
			onDragEnter: jest.fn(),
			onDragLeave: jest.fn(),
			onDragOver: jest.fn(),
			onDrop: jest.fn(),
			onFileInputChange: jest.fn(),
			onImport: jest.fn(),
			onOptionsChange: jest.fn(),
			onSelectFileImporter: jest.fn(),
			onSelectPasteImporter: jest.fn(),
			onPasteTextChange: jest.fn(),
			onSelectPlugin: jest.fn(),
		} );
	} );

	it( 'allows JSON imports that use groups in file when no groups currently exist', () => {
		render( <ImportPage /> );

		expect( screen.getByRole( 'button', { name: 'Preview redirects' } ) ).toBeEnabled();
		expect( screen.getByRole( 'button', { name: 'Import redirects' } ) ).toBeEnabled();
	} );
} );
