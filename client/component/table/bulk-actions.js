/**
 * External dependencies
 */

import React, { useState } from 'react';
import { translate as __ } from 'wp-plugin-lib/locale';

/**
 * @callback BulkCallback
 * @param {string} action
 **/

/**
 * @typedef IdName
 * @property {string} id
 * @property {string} name
 */

/**
 *
 * @param {object} props Component props
 * @param {IdName[]} props.bulk Options
 * @param {boolean} props.disabled
 * @param {BulkCallback} props.onBulk
 */
function BulkActions( props ) {
	const { bulk, disabled, onBulk } = props;
	const [ action, setAction ] = useState( -1 );

	function doAction( ev ) {
		if ( parseInt( action, 10 ) !== -1 ) {
			onBulk( action );
			setAction( -1 );
		}
	}

	return (
		<div className="alignleft actions bulkactions">
			<label htmlFor="bulk-action-selector-top" className="screen-reader-text">
				{ __( 'Select bulk action' ) }
			</label>

			<select
				name="action"
				id="bulk-action-selector-top"
				value={ action }
				disabled={ disabled }
				onChange={ ( ev ) => setAction( ev.target.value ) }
			>
				<option value="-1">{ __( 'Bulk Actions' ) }</option>

				{ bulk.map( ( item ) => (
					<option key={ item.id } value={ item.id }>
						{ item.name }
					</option>
				) ) }
			</select>

			<button
				type="button"
				className="button action"
				disabled={ disabled || parseInt( action, 10 ) === -1 }
				onClick={ doAction }
			>
				{ __( 'Apply' ) }
			</button>
		</div>
	);
}

export default BulkActions;
