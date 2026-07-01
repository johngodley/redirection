import '@testing-library/jest-dom';
import { fireEvent, render, screen, within } from '@testing-library/react';
import type { CardMetaItem } from 'component/import-export/card';
import ExportPage from './index';
import useExportPage from './use-export-page';
import type { ExportFormat, ExportType } from './types';

jest.mock( 'component/import-export/style.scss', () => ( {} ) );
jest.mock( './use-export-page', () => jest.fn() );

const mockUseExportPage = useExportPage as jest.MockedFunction< typeof useExportPage >;

function getSummaryMeta( exportTypes: ExportType[], format: ExportFormat ): CardMetaItem[] {
	const summary: CardMetaItem[] = [
		{
			label: 'Export type',
			value: exportTypes.length === 0 ? 'No export selected' : exportTypes.includes( 'log' ) ? 'Redirects, Redirect logs' : 'Redirects',
		},
	];

	if ( exportTypes.length > 0 ) {
		summary.push( { label: 'Format', value: format === 'json' ? 'JSON' : 'CSV' } );
	}

	return summary;
}

describe( 'ExportPage', () => {
	beforeEach( () => {
		mockUseExportPage.mockImplementation( () => {
			const React = require( 'react' ) as typeof import('react');
			const [ selectedTypes, setSelectedTypes ] = React.useState< ExportType[] >( [] );

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
				availableFormats:
					selectedTypes.length === 0
						? []
						: selectedTypes.length === 1 && selectedTypes[ 0 ] === 'redirect'
							? [ 'json', 'csv', 'apache', 'nginx' ]
							: [ 'json' ],
				groupRows: [ { id: 11, name: 'Imported', moduleName: 'WordPress' } ],
				hasAllTypesSelected: selectedTypes.length === 2,
				hasSelectedTypes: selectedTypes.length > 0,
				summaryMeta: getSummaryMeta( selectedTypes, 'json' ),
				state: {
					selectedTypes,
					redirectScopeType: 'all',
					redirectModule: 'all',
					redirectGroup: 11,
					format: 'json',
					isExporting: false,
					isPreviewLoading: false,
					previewTotal: selectedTypes.length === 0 ? null : selectedTypes.includes( 'log' ) ? 3 : 2,
					previewEstimatedSize: selectedTypes.length === 0 ? null : selectedTypes.includes( 'log' ) ? 2560 : 2048,
					currentError: null,
					lastResult: false,
				},
				onChange: jest.fn(),
				onToggleType: ( nextType: ExportType ) =>
					setSelectedTypes( ( current ) =>
						current.includes( nextType ) ? current.filter( ( item ) => item !== nextType ) : [ ...current, nextType ]
					),
				onToggleAllTypes: ( enabled: boolean ) => setSelectedTypes( enabled ? [ 'redirect', 'log' ] : [] ),
				onDownload: jest.fn(),
				onView: jest.fn(),
			};
		} );
	} );

	it( 'allows export selection to start empty and be built with checkboxes', () => {
		render( <ExportPage /> );

		expect( screen.getByText( 'Export preview' ) ).toBeInTheDocument();
		expect( screen.getByText( 'No export selected' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Select one or more export types to choose a format.' ) ).toBeInTheDocument();

		const logsCard = screen.getByText( 'Redirect logs' ).closest( '.file-sniff__card' );
		expect( logsCard ).not.toBeNull();
		fireEvent.click( within( logsCard as HTMLElement ).getByRole( 'checkbox', { name: 'Include in export' } ) );

		expect( screen.getByText( '1' ) ).toBeInTheDocument();
		expect( screen.getByText( '512 B' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Redirect logs' ) ).toBeInTheDocument();
	} );

	it( 'switches to a combined JSON export when multiple cards are selected', () => {
		render( <ExportPage /> );

		const redirectCard = screen.getByText( 'Redirects' ).closest( '.file-sniff__card' );
		const logsCard = screen.getByText( 'Redirect logs' ).closest( '.file-sniff__card' );

		expect( redirectCard ).not.toBeNull();
		expect( logsCard ).not.toBeNull();

		fireEvent.click( within( redirectCard as HTMLElement ).getByRole( 'checkbox', { name: 'Include in export' } ) );
		fireEvent.click( within( logsCard as HTMLElement ).getByRole( 'checkbox', { name: 'Include in export' } ) );

		expect( screen.getByText( '3' ) ).toBeInTheDocument();
		expect( screen.getByText( '2.5 KB' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Redirects, Redirect logs' ) ).toBeInTheDocument();
	} );

	it( 'toggles all export cards with the select all checkbox', () => {
		render( <ExportPage /> );

		const selectAll = screen.getByRole( 'checkbox', { name: 'Select all' } );
		fireEvent.click( selectAll );

		expect( screen.getByRole( 'checkbox', { name: 'Include in export' } ) ).toBeChecked();
		expect( screen.getAllByRole( 'checkbox', { name: 'Include in export' } )[ 1 ] ).toBeChecked();

		fireEvent.click( selectAll );

		expect( screen.getAllByRole( 'checkbox', { name: 'Include in export' } )[ 0 ] ).not.toBeChecked();
		expect( screen.getAllByRole( 'checkbox', { name: 'Include in export' } )[ 1 ] ).not.toBeChecked();
	} );
} );
