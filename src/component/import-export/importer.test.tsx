import '@testing-library/jest-dom';
import { fireEvent, render, screen } from '@testing-library/react';
import Importer from './importer';

describe( 'Importer', () => {
	it( 'renders richer metadata for plugin imports', () => {
		render(
			<Importer
				plugin={ {
					id: 'wordpress-old-slugs',
					name: 'WordPress permalink redirect',
					description: 'Redirects created by WordPress.',
					source: 'WordPress posts and post meta',
					preview_supported: true,
					total: 7,
				} }
				onSelect={ jest.fn() }
			/>
		);

		expect( screen.getByText( 'Import type' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Redirects created by WordPress.' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Stored in' ) ).toBeInTheDocument();
		expect( screen.getByText( 'WordPress posts and post meta' ) ).toBeInTheDocument();
	} );

	it( 'shows preview support and storage metadata and remains selectable', () => {
		const onSelect = jest.fn();

		render(
			<Importer
				plugin={ {
					id: 'wordpress-old-slugs',
					name: 'WordPress permalink redirect',
					description: 'Redirects created by WordPress.',
					source: 'WordPress posts and post meta',
					preview_supported: true,
					total: 7,
				} }
				onSelect={ onSelect }
			/>
		);

		expect( screen.getByText( 'Redirects created by WordPress.' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Stored in' ) ).toBeInTheDocument();
		expect( screen.getByText( 'WordPress posts and post meta' ) ).toBeInTheDocument();
		expect( screen.getByText( '7' ) ).toBeInTheDocument();

		fireEvent.click( screen.getByRole( 'button', { name: 'Use importer' } ) );
		expect( onSelect ).toHaveBeenCalledTimes( 1 );
	} );

	it( 'allows the active importer to be cleared', () => {
		const onSelect = jest.fn();

		render(
			<Importer
				plugin={ {
					id: 'wordpress-old-slugs',
					name: 'WordPress permalink redirect',
					description: 'Redirects created by WordPress.',
					source: 'WordPress posts and post meta',
					preview_supported: true,
					total: 7,
				} }
				onSelect={ onSelect }
				isActive={ true }
			/>
		);

		fireEvent.click( screen.getByRole( 'button', { name: 'Clear importer' } ) );
		expect( onSelect ).toHaveBeenCalledTimes( 1 );
	} );
} );
