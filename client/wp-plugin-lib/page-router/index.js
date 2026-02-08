/**
 * External dependencies
 */

import { useEffect, useRef } from 'react';

/**
 * Internal dependencies
 */
import { getWordPressUrl, getPluginPage } from '@wp-plugin-lib';

/**
 *
 * @param {object} props Component props
 * @param {string} props.page Current page
 * @param {} props.setPage Change page
 * @param {} props.onPageChange Callback when page changes
 * @param {string} props.defaultPage Default page
 * @param {string} props.baseUrl Base URL for the page
 * @param {string[]} props.allowedPages Allowed page values
 * @param {} props.children
 */
function PageRouter( props ) {
	const { page, setPage, children, onPageChange, defaultPage, baseUrl, allowedPages } = props;
	const previousPage = useRef();

	function onPageChanged() {
		const page = getPluginPage( window.Redirectioni10n?.caps?.pages || [] );

		setPage( page );
	}

	useEffect(() => {
		window.addEventListener( 'popstate', onPageChanged );

		return () => {
			window.removeEventListener( 'popstate', onPageChanged );
		};
	}, []);

	useEffect(() => {
		onPageChange();

		if ( previousPage.current && previousPage.current !== page ) {
			history.pushState(
				{},
				'',
				getWordPressUrl( { sub: page }, { sub: defaultPage }, baseUrl )
			);
		}

		previousPage.current = page;
	}, [ page ]);

	return children;
}

export default PageRouter;
