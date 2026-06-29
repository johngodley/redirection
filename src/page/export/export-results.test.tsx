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
					type: 'redirect',
					format: 'json',
					data: '',
					total: 5348,
				} }
			/>
		);

		expect( screen.getByText( '5,348' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Items exported' ) ).toBeInTheDocument();
	} );

	it( 'renders the viewed export output inside the result card', () => {
		const { container } = render(
			<ExportResults
				lastResult={ {
					action: 'view',
					type: 'redirect',
					format: 'csv',
					data: 'source,target',
					total: 2,
				} }
			/>
		);

		const card = container.querySelector( '.file-sniff__card' );
		expect( card ).not.toBeNull();
		expect( within( card as HTMLElement ).getByDisplayValue( 'source,target' ) ).toBeInTheDocument();
		expect( within( card as HTMLElement ).getByText( 'View results' ) ).toBeInTheDocument();
	} );
} );
