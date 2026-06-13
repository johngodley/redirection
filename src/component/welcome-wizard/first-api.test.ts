import getFirstApi, { hasWorkingApi } from './first-api';

describe( 'welcome wizard first API helpers', () => {
	it( 'returns the first fully working API', () => {
		const apiTest = {
			0: {
				GET: { status: 'fail' },
				POST: { status: 'ok' },
			},
			3: {
				GET: { status: 'ok' },
				POST: { status: 'ok' },
			},
		};

		expect( getFirstApi( apiTest ) ).toBe( '3' );
		expect( hasWorkingApi( apiTest ) ).toBe( true );
	} );

	it( 'returns null when no API fully works', () => {
		const apiTest = {
			0: {
				GET: { status: 'fail' },
				POST: { status: 'fail' },
			},
			3: {
				GET: { status: 'ok' },
				POST: { status: 'fail' },
			},
		};

		expect( getFirstApi( apiTest ) ).toBeNull();
		expect( hasWorkingApi( apiTest ) ).toBe( false );
	} );
} );
