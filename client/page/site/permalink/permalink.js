/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';

function Permalink( { link, onChange, onDelete } ) {
	const deleteIt = ( ev ) => {
		ev.preventDefault();
		onDelete();
	};

	return (
		<tr className="redirect-alias__item">
			<td>
				<input className="regular-text" type="text" name="link" value={ link } onChange={ onChange } />
			</td>
			<td className="redirect-alias__delete">
				<button onClick={ deleteIt }>
					<span className="dashicons dashicons-trash" />
				</button>
			</td>
		</tr>
	);
};

export default Permalink;
