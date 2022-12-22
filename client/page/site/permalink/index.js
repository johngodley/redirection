/**
 * External dependencies
 */

import React from 'react';
import { __ } from '@wordpress/i18n';

/**
 * Local dependencies
 */

import Permalink from './permalink';

const PERMALINK_MAX = 10;

const addPermalink = ( permalinks, onChange, ev ) => {
	ev.preventDefault();

	onChange( { permalinks: permalinks.concat( '' ).slice( 0, PERMALINK_MAX ) } );
};

const removePermalink = ( pos, permalinks, onChange ) => {
	onChange( { permalinks: [ ...permalinks.slice( 0, pos ), ...permalinks.slice( pos + 1 ) ] } );
};

const updatePermalink = ( pos, permalinks, onChange, ev ) => {
	const updated = permalinks.slice();

	updated[ pos ] = ev.target.value;

	onChange( { permalinks: updated } );
};

function PermalinkSettings( props ) {
	const { permalinks, onChange } = props;

	return (
		<>
			<h3>{ __( 'Permalink Migration', 'redirection' ) }</h3>
			<p>{ __( 'Enter old permalinks structures to automatically migrate them to your current one.', 'redirection' ) }</p>
			<p>{ __( 'Note: this is in beta and will only migrate posts. Certain permalinks will not work. If yours does not work then you will need to wait until it is out of beta.', 'redirection' ) }</p>

			<table className="wp-list-table widefat fixed striped items redirect-aliases table-auto">
				<thead>
					<tr>
						<th>{ __( 'Permalinks', 'redirection' ) }</th>
						<th className="redirect-alias__delete" />
					</tr>
				</thead>

				<tbody>
					{ permalinks.map( ( link, key ) => (
						<Permalink
							key={ key }
							link={ link }
							onChange={ ( ev ) => updatePermalink( key, permalinks, onChange, ev ) }
							onDelete={ () => removePermalink( key, permalinks, onChange ) }
						/>
					) ) }
					{ permalinks.length === 0 && (
						<tr>
							<td colSpan={ 2 }>{ __( 'No migrated permalinks', 'redirection' ) }</td>
						</tr>
					) }
				</tbody>
			</table>

			<p>
				<button className="button-secondary" onClick={ ( ev ) => addPermalink( permalinks, onChange, ev ) }>
					{ __( 'Add Permalink', 'redirection' ) }
				</button>
			</p>
		</>
	);
}

export default PermalinkSettings;
