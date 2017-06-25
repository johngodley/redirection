/* global Redirectioni10n */
/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'lib/locale';

const ExportCSV = props => {
	const { logType } = props;

	return (
		<form method="post" action={ Redirectioni10n.pluginRoot + '&sub=' + logType }>
			<input type="hidden" name="_wpnonce" value={ Redirectioni10n.WP_API_nonce } />
			<input type="hidden" name="export-csv" value="" />
			<input className="button" type="submit" name="" value={ __( 'Export to CSV' ) } onClick={ this.onShow } />
		</form>
	);
};

export default ExportCSV;
