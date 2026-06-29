import '@testing-library/jest-dom';
import { render, screen } from '@testing-library/react';
import ExportSummaryCard from './export-summary-card';

window.Redirectioni10n = {
	locale: 'en-US',
} as Redirectioni10n;

describe( 'ExportSummaryCard', () => {
	it( 'renders preview details and totals', () => {
		render(
			<ExportSummaryCard
				meta={ [
					{ label: 'Export type', value: 'Redirects' },
					{ label: 'Scope', value: 'Everything' },
					{ label: 'Format', value: 'JSON' },
				] }
				isPreviewLoading={ false }
				previewTotal={ 12 }
				previewEstimatedSize={ 2048 }
			/>
		);

		expect( screen.getByText( 'Export preview' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Preview' ) ).toBeInTheDocument();
		expect( screen.getByText( '12' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Items available' ) ).toBeInTheDocument();
		expect( screen.getByText( '2.0 KB' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Approximate file size' ) ).toBeInTheDocument();
	} );

	it( 'shows loading state without totals', () => {
		render(
			<ExportSummaryCard
				meta={ [ { label: 'Export type', value: '404 logs' } ] }
				isPreviewLoading={ true }
				previewTotal={ null }
				previewEstimatedSize={ null }
			/>
		);

		expect( screen.getByText( 'Loading' ) ).toBeInTheDocument();
		expect( screen.queryByText( 'Items available' ) ).not.toBeInTheDocument();
	} );

	it( 'omits file size when only the total is available', () => {
		render(
			<ExportSummaryCard
				meta={ [ { label: 'Export type', value: 'Redirect logs' } ] }
				isPreviewLoading={ false }
				previewTotal={ 3 }
				previewEstimatedSize={ null }
			/>
		);

		expect( screen.getByText( '3' ) ).toBeInTheDocument();
		expect( screen.queryByText( 'Approximate file size' ) ).not.toBeInTheDocument();
	} );

	it( 'formats item counts using the configured locale', () => {
		render(
			<ExportSummaryCard
				meta={ [ { label: 'Export type', value: 'Redirects' } ] }
				isPreviewLoading={ false }
				previewTotal={ 1234 }
				previewEstimatedSize={ null }
			/>
		);

		expect( screen.getByText( '1,234' ) ).toBeInTheDocument();
	} );
} );
