import '@testing-library/jest-dom';
import { render, screen, within } from '@testing-library/react';
import ExportResults from './export-results';

window.Redirectioni10n = {
	locale: 'en-US',
} as Redirectioni10n;

describe( 'ExportResults', () => {
	it( 'formats exported item counts using the configured locale', () => {
		render(
			<ExportResults
				lastResult={ {
					action: 'download',
					types: [ 'redirect' ],
					format: 'json',
					data: '',
					total: 5348,
					skipped: null,
				} }
			/>
		);

		expect( screen.getByText( '5,348' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Items exported' ) ).toBeInTheDocument();
		expect( screen.getByText( 'File size' ) ).toBeInTheDocument();
		expect( screen.getByText( '0 B' ) ).toBeInTheDocument();
	} );

	it( 'renders the viewed export output inside the result card', () => {
		const { container } = render(
			<ExportResults
				lastResult={ {
					action: 'view',
					types: [ 'redirect' ],
					format: 'csv',
					data: 'source,target',
					total: 2,
					skipped: null,
				} }
			/>
		);

		const card = container.querySelector( '.file-sniff__card' );
		expect( card ).not.toBeNull();
		expect( within( card as HTMLElement ).getByDisplayValue( 'source,target' ) ).toBeInTheDocument();
		expect( within( card as HTMLElement ).getByText( 'View results' ) ).toBeInTheDocument();
		expect( within( card as HTMLElement ).getByText( '13 B' ) ).toBeInTheDocument();
	} );

	it( 'shows a skipped-item stat when the export reports skipped items', () => {
		render(
			<ExportResults
				lastResult={ {
					action: 'download',
					types: [ 'redirect' ],
					format: 'redirects-file',
					data: '',
					total: 42,
					skipped: 5,
				} }
			/>
		);

		expect( screen.getByText( 'Skipped (unsupported for this format)' ) ).toBeInTheDocument();
		expect( screen.getByText( '5' ) ).toBeInTheDocument();
		expect( screen.getByText( '37' ) ).toBeInTheDocument();
		expect( screen.queryByText( '42' ) ).not.toBeInTheDocument();
	} );

	it( 'omits the skipped-item stat when nothing was skipped', () => {
		render(
			<ExportResults
				lastResult={ {
					action: 'download',
					types: [ 'redirect' ],
					format: 'json',
					data: '',
					total: 42,
					skipped: 0,
				} }
			/>
		);

		expect( screen.queryByText( 'Skipped (unsupported for this format)' ) ).not.toBeInTheDocument();
	} );

	it( 'uses the explicit exported count when disabled redirects were excluded without being skipped', () => {
		render(
			<ExportResults
				lastResult={ {
					action: 'download',
					types: [ 'redirect' ],
					format: 'redirects-file',
					data: '',
					total: 42,
					skipped: 5,
					// 42 total, 5 skipped (unsupported), and 7 more excluded for being
					// disabled - total - skipped would wrongly report 37 "exported".
					exported: 30,
				} }
			/>
		);

		expect( screen.getByText( '30' ) ).toBeInTheDocument();
		expect( screen.queryByText( '37' ) ).not.toBeInTheDocument();
	} );
} );
