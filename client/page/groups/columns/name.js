/**
 * External dependencies
 */

import React from 'react';
import Highlighter from 'react-highlight-words';

function NameColumn( { row, filters } ) {
	const { enabled, name } = row;

	if ( enabled ) {
		return <Highlighter searchWords={ [ filters.name ] } textToHighlight={ name } autoEscape />;
	}

	return <strike>{ name }</strike>;
}

export default NameColumn;
