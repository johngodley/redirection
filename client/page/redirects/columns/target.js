/**
 * External dependencies
 */

import React from 'react';
import Highlighter from 'react-highlight-words';

/**
 * Internal dependencies
 */

import { MATCH_URL } from 'state/redirect/selector';

function Target( props ) {
	const { row, filters } = props;
	const { match_type, action_data } = row;

	if ( match_type === MATCH_URL ) {
		return (
			<span className="target">
				<Highlighter searchWords={ [ filters.target ] } textToHighlight={ action_data.url || '' } autoEscape />
			</span>
		);
	}

	return null;
}

export default Target;
