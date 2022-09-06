/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import Options from '../options';
import Support from '../support';
import Site from '../site';
import Logs from '../logs';
import Logs404 from '../logs404';
import ImportExport from '../io';
import Groups from '../groups';
import Redirects from '../redirects';

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
