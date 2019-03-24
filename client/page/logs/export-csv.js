/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';

import { getApiNonce } from 'lib/api';

const ExportCSV = ( { logType, title } ) => {
	return (
		<form method="post" action={ Redirectioni10n.pluginRoot + '&sub=' + logType }>
			<input type="hidden" name="_wpnonce" value={ getApiNonce() } />
			<input type="hidden" name="export-csv" value="" />
			<input className="button" type="submit" name="" value={ title } />
		</form>
	);
};

export default ExportCSV;
