/**
 * External dependencies
 */

import React from 'react';
import Highlighter from 'react-highlight-words';

/**
 * Internal dependencies
 */
import { ExternalLink } from 'wp-plugin-components';

/**
 *
 * @param {object} props - Component props
 * @param {object} props.filters - Filters
 * @param {TableRow} props.row - Row
 */
function ColumnTarget( props ) {
	const { filters, row } = props;
	const { sent_to } = row;

	if ( ! sent_to ) {
		return null;
	}

	return (
		<ExternalLink url={ sent_to }>
			<Highlighter
				searchWords={ [ filters.target ] }
				textToHighlight={ sent_to.substring( 0, 100 ) }
				autoEscape
			/>
		</ExternalLink>
	);
}

export default ColumnTarget;
