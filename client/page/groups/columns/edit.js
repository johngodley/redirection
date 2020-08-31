/**
 * External dependencies
 */

import React, { useState } from 'react';
import { translate as __ } from 'i18n-calypso';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */
import { Select } from 'wp-plugin-components';
import { updateGroup } from 'state/group/action';
import { getModules } from 'state/io/selector';

/**
 * Edit a group
 * @param {object} props - Component props
 * @param {object} props.group - The group
 * @param {} props.onCancel - Cancel the edit
 * @oaran {} props.onSaveGroup - Save the group
 */
function EditColumn( props ) {
	const { group, onCancel, onSaveGroup } = props;
	const [ name, setName ] = useState( group.name );
	const [ moduleId, setModuleId ] = useState( group.module_id );

	function onSave( ev ) {
		ev.preventDefault();
		ev.stopPropagation();

		onCancel();
		onSaveGroup( group.id, { id: group.id, name, moduleId } );
	}

	return (
		<form onSubmit={ onSave }>
			<table className="edit-groups">
				<tbody>
					<tr>
						<th>{ __( 'Name' ) }</th>
						<td>
							<input
								type="text"
								className="regular-text"
								name="name"
								value={ name }
								onChange={ ( ev ) => setName( ev.target.value ) }
							/>
						</td>
					</tr>
					<tr>
						<th>{ __( 'Module' ) }</th>
						<td>
							<Select
								name="module_id"
								value={ moduleId }
								onChange={ ( ev ) => setModuleId( parseInt( ev.target.value, 10 ) ) }
								items={ getModules() }
							/>
						</td>
					</tr>
					<tr>
						<th />
						<td>
							<div className="table-actions">
								<input
									className="button-primary"
									type="submit"
									name="save"
									value={ __( 'Save' ) }
								/>
								&nbsp;
								<input
									className="button-secondary"
									type="button"
									name="cancel"
									value={ __( 'Cancel' ) }
									onClick={ onCancel }
								/>
							</div>

							{ moduleId === 2 && (
								<p>
									<br />
									{ __(
										'Note that you will need to set the Apache module path in your Redirection options.'
									) }
								</p>
							) }
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	);
}

function mapDispatchToProps( dispatch ) {
	return {
		onSaveGroup: ( id, item ) => {
			dispatch( updateGroup( id, item ) );
		},
	};
}

export default connect(
	null,
	mapDispatchToProps
)( EditColumn );
