/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';

/**
 * Internal dependencies
 */
import Options from 'page/options';
import Support from 'page/support';
import Site from 'page/site';
import Logs from 'page/logs';
import Logs404 from 'page/logs404';
import ImportExport from 'page/io';
import Groups from 'page/groups';
import Redirects from 'page/redirects';

/**
 * Display page content
 * @param {object} props Props
 * @param {string} props.page Page ID
 * @returns object
 */
function PageContent( { page } ) {
	switch ( page ) {
		case 'support':
			return <Support />;

		case '404s':
			return <Logs404 />;

		case 'log':
			return <Logs />;

		case 'io':
			return <ImportExport />;

		case 'groups':
			return <Groups />;

		case 'options':
			return <Options />;

		case 'site':
			return <Site />;
	}

	return <Redirects />;
}

export default PageContent;
