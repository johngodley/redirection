/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'wp-plugin-lib/locale';
import Highlighter from 'react-highlight-words';

function DropdownSuggestions( props ) {
	const { options, value, onSelect, onClose } = props;

	function onClick( ev, item ) {
		ev.preventDefault();
		onSelect( item );
		onClose();
	}

	return (
		<ul>
			{ options.map( ( item, pos ) => (
				<li key={ pos }>
					<a href="#" onClick={ ( ev ) => onClick( ev, item.value ) }>
						<Highlighter
							searchWords={ [ value ] }
							textToHighlight={ item.title }
							autoEscape
						/>
					</a>
				</li>
			) ) }
		</ul>
	);
}

export default DropdownSuggestions;
