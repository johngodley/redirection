/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';

/**
 * Internal dependencies
 */

import { isEnabled } from 'component/table/utils';
import SourceName from './source-name';
import Target from './target';
import SourceFlags from './source-flags';
import SourceQuery from './source-query';

function SourceColumn( props ) {
	const { row, table, defaultFlags } = props;
	const { displaySelected } = table;

	return (
		<div className="redirect-column-wrap">
			<div className="redirect-source__details">
				<SourceName row={ row } displaySelected={ displaySelected } filters={ table.filterBy } />

				{ isEnabled( displaySelected, 'target' ) && <Target row={ row } filters={ table.filterBy } /> }
			</div>

			<div className="redirect-source__flags">
				{ isEnabled( displaySelected, 'flags' ) && <SourceFlags row={ row } defaultFlags={ defaultFlags } /> }
				{ isEnabled( displaySelected, 'query' ) && <SourceQuery row={ row } defaultFlags={ defaultFlags } /> }
			</div>
		</div>
	);
}

export default SourceColumn;
