import { QueryClientProvider } from '@tanstack/react-query';
import { ReactQueryDevtools } from '@tanstack/react-query-devtools';
import Home from './page/home';
import apiFetch from '@wp-plugin-lib/api-fetch';
import { queryClient } from 'lib/query-client';

// Validate the locale works with the browser
try {
	new Intl.NumberFormat( window.Redirectioni10n.locale );
} catch ( error ) {
	window.Redirectioni10n.locale = 'en-US';
}

// Set API nonce and root URL
apiFetch.resetMiddlewares();
apiFetch.use( apiFetch.createRootURLMiddleware( window.Redirectioni10n?.api?.WP_API_root ?? '/wp-json/' ) );
apiFetch.use( apiFetch.createNonceMiddleware( window.Redirectioni10n?.api?.WP_API_nonce ?? '' ) );

export default function App(): React.ReactElement {
	return (
		<QueryClientProvider client={ queryClient }>
			<Home />
			{ process.env.NODE_ENV === 'development' && <ReactQueryDevtools initialIsOpen={ false } /> }
		</QueryClientProvider>
	);
}
