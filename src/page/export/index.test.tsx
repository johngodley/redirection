import '@testing-library/jest-dom';
import { fireEvent, render, screen, within } from '@testing-library/react';
import type { CardMetaItem } from 'component/import-export/card';
import ExportPage from './index';
import useExportPage from './use-export-page';
import type { ExportFormat, ExportType } from './types';

jest.mock( 'component/import-export/style.scss', () => ( {} ) );
jest.mock( './use-export-page', () => jest.fn() );

const mockUseExportPage = useExportPage as jest.MockedFunction< typeof useExportPage >;

function getSummaryMeta( exportType: ExportType, format: ExportFormat ): CardMetaItem[] {
	return [
		{ label: 'Export type', value: exportType === 'redirect' ? 'Redirects' : 'Redirect logs' },
		{ label: 'Format', value: format === 'json' ? 'JSON' : 'CSV' },
	];
}

describe( 'ExportPage', () => {
	beforeEach( () => {
		mockUseExportPage.mockImplementation( () => {
			const React = require( 'react' ) as typeof import('react');
			const [ exportType, setExportType ] = React.useState< ExportType >( 'redirect' );

			return {
				exportTypes: [
					{
						id: 'redirect',
						name: 'Redirects',
						description: 'Export redirect rules.',
						formats: [ 'json', 'csv', 'apache', 'nginx' ],
					},
					{
						id: 'log',
						name: 'Redirect logs',
						description: 'Export redirect activity logs.',
						formats: [ 'json', 'csv' ],
					},
				],
				availableFormats: exportType === 'redirect' ? [ 'json', 'csv', 'apache', 'nginx' ] : [ 'json', 'csv' ],
				groupRows: [ { id: 11, name: 'Imported', moduleName: 'WordPress' } ],
				summaryMeta: getSummaryMeta( exportType, 'json' ),
				state: {
					exportType,
					redirectScopeType: 'all',
					redirectModule: 'all',
					redirectGroup: 11,
					format: 'json',
					isExporting: false,
					isPreviewLoading: false,
					previewTotal: exportType === 'redirect' ? 2 : 1,
					previewEstimatedSize: exportType === 'redirect' ? 2048 : 512,
					lastResult: false,
				},
				onChange: jest.fn(),
				onSelectType: ( nextType: ExportType ) => setExportType( nextType ),
				onDownload: jest.fn(),
				onView: jest.fn(),
			};
		} );
	} );

	it( 'switches summary details when the export type changes', () => {
		render( <ExportPage /> );

		expect( screen.getByText( 'Export preview' ) ).toBeInTheDocument();
		expect( screen.getByText( '2.0 KB' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Items available' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Approximate file size' ) ).toBeInTheDocument();

		const logsCard = screen.getByText( 'Redirect logs' ).closest( '.file-sniff__card' );
		expect( logsCard ).not.toBeNull();
		fireEvent.click( within( logsCard as HTMLElement ).getByRole( 'button', { name: 'Use export' } ) );

		expect( screen.getByText( '1' ) ).toBeInTheDocument();
		expect( screen.getByText( '512 B' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Approximate file size' ) ).toBeInTheDocument();
	} );
} );
