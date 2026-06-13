import '@testing-library/jest-dom';
import { render, screen } from '@testing-library/react';
import ApiResultError from './api-result-error';

jest.mock(
	'@wp-plugin-components/error/decode-error',
	() => ( {
		__esModule: true,
		default: ( { error }: { error: { request?: { origins?: { current?: string; test?: string } } } } ) => (
			<div>
				{ error.request?.origins?.current && <span>Current admin page origin:</span> }
				{ error.request?.origins?.test && <span>REST API origin:</span> }
			</div>
		),
	} ),
	{ virtual: true }
);

( global as any ).Redirectioni10n = {
	api: {
		WP_API_root: 'https://example.com/wp-json/',
		site_health: 'https://example.com/wp-admin/site-health.php',
	},
	versions: 'WordPress 6.8, PHP 8.2, Redirection 5.8.0',
	version: '5.8.0',
};

describe( 'ApiResultError', () => {
	it( 'shows blocked labels for origin mismatch errors', () => {
		render(
			<ApiResultError
				methods={ [ 'GET', 'POST' ] }
				error={ {
					code: 'rest_api_cors_mismatch',
					message:
						'This REST API URL uses a different origin and cannot be tested from the current admin page.',
					data: { status: 0 },
					request: {
						origins: {
							current: 'http://release.local:10029',
							test: 'http://release.local',
						},
					},
				} }
			/>
		);

		expect( screen.getByText( 'GET blocked' ) ).toBeInTheDocument();
		expect( screen.getByText( 'POST blocked' ) ).toBeInTheDocument();
		expect( screen.queryByText( 'GET 0' ) ).not.toBeInTheDocument();
		expect( screen.queryByText( 'POST 0' ) ).not.toBeInTheDocument();
		expect( screen.getByText( 'Current admin page origin:' ) ).toBeInTheDocument();
		expect( screen.getByText( 'REST API origin:' ) ).toBeInTheDocument();
	} );

	it( 'shows HTTP status codes for normal REST errors', () => {
		render(
			<ApiResultError
				methods={ [ 'GET' ] }
				error={ {
					code: 'rest_forbidden',
					message: 'Sorry, you are not allowed to do that.',
					data: { status: 403 },
				} }
			/>
		);

		expect( screen.getByText( 'GET 403' ) ).toBeInTheDocument();
	} );
} );
