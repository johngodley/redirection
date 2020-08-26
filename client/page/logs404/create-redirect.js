/**
 * External dependencies
 */

import React, { useState } from 'react';
import { translate as __ } from 'i18n-calypso';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */

import EditRedirect from 'component/redirect-edit';
import Modal from 'wp-plugin-components/modal';
import { getDefaultItem } from 'state/redirect/selector';
import { getFlags } from 'state/settings/selector';
import { has_capability, CAP_404_DELETE } from 'lib/capabilities';
import { performTableAction } from 'state/error/action';

function getRowForId( url, rows ) {
	const rowUrl = rows.find( ( row ) => row.id === url || row.id === parseInt( url, 10 ) );

	if ( rowUrl ) {
		return rowUrl.url;
	}

	return url;
}

function getUniqueUrls( urls, rows ) {
	if ( ! urls ) {
		return '';
	}

	if ( ! Array.isArray( urls ) ) {
		return urls;
	}

	return [ ...new Set( urls.map( ( url ) => getRowForId( url, rows ) ) ) ];
}

function CreateRedirect( props ) {
	const { onClose, redirect, defaultFlags, onDelete, rows } = props;
	const uniqueUrls = getUniqueUrls( redirect.url, rows );
	const [ deleteLog, setDeleteLog ] = useState( false );
	const item = { ...getDefaultItem( uniqueUrls, 0, defaultFlags ), ...redirect, url: uniqueUrls };

	return (
		<Modal onClose={ onClose } padding>
			<div className="add-new">
				<EditRedirect
					item={ item }
					saveButton={ __( 'Add Redirect' ) }
					onCancel={ onClose }
					childSave={ () => deleteLog && onDelete( Array.isArray( uniqueUrls ) ? uniqueUrls : [ uniqueUrls ] ) }
					canSave={ ( multi ) => deleteLog && confirm( multi ? __( 'Are you sure you want to delete the selected items?' ) : __( 'Are you sure you want to delete this item?' ) ) }
					autoFocus
				>
					{ has_capability( CAP_404_DELETE ) && (
						<tr>
							<th>{ __( 'Delete Log Entries' ) }</th>
							<td className="edit-left" style={ { padding: '7px 0px' } }>
								<label>
									<input
										type="checkbox"
										checked={ deleteLog }
										onChange={ ( ev ) => setDeleteLog( ev.target.checked ) }
									/>

									{ uniqueUrls.length <= 1
										? __( 'Delete logs for this entry' )
										: __( 'Delete logs for these entries' ) }
								</label>
							</td>
						</tr>
					) }
				</EditRedirect>
			</div>
		</Modal>
	);
}

function mapDispatchToProps( dispatch ) {
	return {
		onDelete: ( urls ) => {
			dispatch( performTableAction( 'delete', urls, { groupBy: 'url-exact', deleteConfirm: true } ) );
		},
	};
}

function mapStateToProps( state ) {
	const { rows } = state.error;

	return {
		defaultFlags: getFlags( state ),
		rows,
	};
}

export default connect(
	mapStateToProps,
	mapDispatchToProps
)( CreateRedirect );
