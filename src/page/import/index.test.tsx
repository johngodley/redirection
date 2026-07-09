import '@testing-library/jest-dom';
import { render, screen } from '@testing-library/react';
import ImportPage from './index';
import useImportPage from './use-import-page';

jest.mock( 'component/import-export/style.scss', () => ( {} ) );
jest.mock( './use-import-page', () => jest.fn() );

const mockUseImportPage = useImportPage as jest.MockedFunction< typeof useImportPage >;

const getImportPageValue = () => ( {
	state: {
		activeImportType: 'file',
		activePluginId: null,
		group: 0,
		hover: false,
		file: new File( [ '{}' ], 'redirects.json', { type: 'application/json' } ),
		pasteFile: false,
		pasteText: '',
		duplicateMode: 'import' as const,
		deleteSource: false,
		fileInfo: {
			format: 'json' as const,
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
	importingStatus: 'idle' as const,
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

describe( 'ImportPage', () => {
	beforeEach( () => {
		mockUseImportPage.mockReturnValue( getImportPageValue() );
	} );

	it( 'allows JSON imports that use groups in file when no groups currently exist', () => {
		render( <ImportPage /> );

		expect( screen.getByRole( 'button', { name: 'Preview' } ) ).toBeEnabled();
		expect( screen.getByRole( 'button', { name: 'Import' } ) ).toBeEnabled();
	} );

	it( 'shows an empty options message when no import source is selected', () => {
		const value = getImportPageValue();

		mockUseImportPage.mockReturnValue( {
			...value,
			state: {
				...value.state,
				activeImportType: null,
				hasActiveImport: false,
				file: false,
				fileInfo: null,
				selectedSections: [],
			},
		} );

		render( <ImportPage /> );

		expect( screen.getByText( 'Select an import source to see available options.' ) ).toBeInTheDocument();
		expect( screen.getByRole( 'button', { name: 'Preview' } ) ).toBeDisabled();
		expect( screen.getByRole( 'button', { name: 'Import' } ) ).toBeDisabled();
	} );

	it( 'does not show JSON contents options when a plugin importer is selected', () => {
		const value = getImportPageValue();

		mockUseImportPage.mockReturnValue( {
			...value,
			state: {
				...value.state,
				activeImportType: 'plugin',
				activePluginId: 'wordpress-old-slugs',
			},
		} );

		render( <ImportPage /> );

		expect( screen.queryByText( 'Contents' ) ).not.toBeInTheDocument();
		expect( screen.getByText( 'Delete original data' ) ).toBeInTheDocument();
	} );
} );
