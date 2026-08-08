import '@testing-library/jest-dom';
import { render, screen } from '@testing-library/react';
import ImportOptions from './import-options';

describe( 'ImportOptions', () => {
	const baseProps = {
		activeImportType: 'file' as const,
		activePluginId: null,
		file: new File( [ '/old /new 301' ], '_redirects' ),
		deleteSource: false,
		duplicateMode: 'import' as const,
		group: 0,
		groupRows: [ { id: 1, name: 'Redirections' } ],
		isJsonFile: () => false,
		selectedSections: [],
		onChange: jest.fn(),
	};

	it( 'shows redirect options for a valid _redirects file', () => {
		render(
			<ImportOptions
				{ ...baseProps }
				fileInfo={ { format: 'redirects-file', valid: true, importSupported: true, rules: 1 } }
			/>
		);

		expect( screen.getByText( 'Group' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Duplicates' ) ).toBeInTheDocument();
	} );

	it( 'hides redirect options for an invalid _redirects file', () => {
		render(
			<ImportOptions
				{ ...baseProps }
				fileInfo={ { format: 'redirects-file', valid: false, error: 'unknown-redirects-file-layout' } }
			/>
		);

		expect( screen.getByText( 'Select an import source to see available options.' ) ).toBeInTheDocument();
	} );
} );
