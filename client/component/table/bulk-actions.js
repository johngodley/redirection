/**
 * External dependencies
 */

import React, { useState } from 'react';
import { translate as __ } from 'i18n-calypso';

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
	const { bulk, disabled, onBulk, isEverything = false } = props;
	const [ action, setAction ] = useState( -1 );

	function doAction( ev ) {
		if ( parseInt( action, 10 ) !== -1 ) {
			onBulk( action, isEverything );
			setAction( -1 );
		}
	}

	return (
		<div className="alignleft actions bulkactions">
			<select
				name="action"
				value={ action }
				disabled={ disabled }
				onChange={ ( ev ) => setAction( ev.target.value ) }
				title={
					isEverything
						? __( 'Actions applied to everything that matches current filter' )
						: __( 'Actions applied to all selected items' )
				}
			>
				<option value="-1">{ isEverything ? __( 'Bulk Actions (all)' ) : __( 'Bulk Actions' ) }</option>

				{ bulk
					.filter( ( item ) => item.isEverything === undefined || item.isEverything === true || ! isEverything )
					.map( ( item ) => (
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
				{ isEverything ? __( 'Apply To All' ) : __( 'Apply' ) }
			</button>
		</div>
	);
}

export default BulkActions;
