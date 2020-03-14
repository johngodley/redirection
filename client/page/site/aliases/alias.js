/**
 * External dependencies
 */

import React, { Fragment } from 'react';
import { translate as __ } from 'lib/locale';

const Alias = ( { domain, asDomain, onChange, onDelete, site } ) => {
	const deleteIt = ev => {
		ev.preventDefault();
		onDelete();
	};

	return (
		<tr className="redirect-alias__item">
			<td><input className="regular-text" type="text" name="domain" value={ domain } onChange={ onChange } /></td>
			<td className="redirect-alias__item__asdomain">
				{ domain.length > 0 && (
					<Fragment>
						<code>{ asDomain }</code> ⇒ <code>{ site }</code>
					</Fragment>
				) }
			</td>
			<td className="redirect-alias__delete"><button onClick={ deleteIt }><span className="dashicons dashicons-trash"></span></button></td>
		</tr>
	);
};

export default Alias;
