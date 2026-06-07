import { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { Button, Select } from '@wp-plugin-components';
import { getModules } from 'lib/modules';
import { useGroupUpdate } from 'lib/api/hooks/use-group-mutations';

interface Group {
	id: number;
	name: string;
	module_id: number;
}

interface EditColumnProps {
	group: Group;
	onCancel: () => void;
}

function EditColumn( props: EditColumnProps ) {
	const { group, onCancel } = props;
	const [ name, setName ] = useState( group.name );
	const [ moduleId, setModuleId ] = useState( group.module_id );
	const { mutate: updateGroup } = useGroupUpdate();

	function onSave( ev: React.FormEvent ) {
		ev.preventDefault();
		ev.stopPropagation();

		onCancel();
		updateGroup( { id: group.id, name, moduleId } );
	}

	return (
		<form onSubmit={ onSave }>
			<table className="edit-groups inline-edit-row">
				<tbody>
					<tr>
						<th>{ __( 'Name', 'redirection' ) }</th>
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
						<th>{ __( 'Module', 'redirection' ) }</th>
						<td>
							<Select
								name="module_id"
								value={ String( moduleId ) }
								onChange={ ( ev ) => setModuleId( parseInt( ev.target.value, 10 ) ) }
								items={ getModules().map( ( m ) => ( { label: m.label, value: String( m.value ) } ) ) }
							/>
						</td>
					</tr>
					<tr>
						<th />
						<td>
							<div className="table-actions">
								<Button isPrimary isSecondary={ false } isSubmit>
									{ __( 'Save', 'redirection' ) }
								</Button>
								&nbsp;
								<Button onClick={ onCancel }>{ __( 'Cancel', 'redirection' ) }</Button>
							</div>

							{ moduleId === 2 && (
								<p>
									<br />
									{ __(
										'Note that you will need to set the Apache module path in your Redirection options.',
										'redirection'
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

export default EditColumn;
