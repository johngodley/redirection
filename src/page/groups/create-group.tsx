import { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { Select } from '@wp-plugin-components';
import { getModules } from 'lib/modules';
import { useGroupCreate } from 'lib/api/hooks/use-group-mutations';

interface CreateGroupProps {
	disabled: boolean;
}

function CreateGroup( props: CreateGroupProps ) {
	const { disabled } = props;
	const [ name, setName ] = useState( '' );
	const [ moduleId, setModuleId ] = useState( 1 );
	const { mutate: createGroup } = useGroupCreate();

	function onSubmit( ev: React.FormEvent ) {
		ev.preventDefault();
		createGroup( { name, module_id: moduleId, position: 0 } );
		setName( '' );
	}

	return (
		<>
			<h2>{ __( 'Add Group', 'redirection' ) }</h2>
			<p>
				{ __(
					'Use groups to organise your redirects. Groups are assigned to a module, which affects how the redirects in that group work. If you are unsure then stick to the WordPress module.',
					'redirection'
				) }
			</p>

			<form onSubmit={ onSubmit }>
				<table className="form-table redirect-groups">
					<tbody>
						<tr>
							<th>{ __( 'Name', 'redirection' ) }</th>
							<td>
								<input
									size={ 30 }
									className="regular-text"
									type="text"
									name="name"
									value={ name }
									onChange={ ( ev ) => setName( ev.target.value ) }
									disabled={ disabled }
								/>
								<Select
									name="group"
									value={ String( moduleId ) }
									onChange={ ( ev ) => setModuleId( parseInt( ev.target.value, 10 ) ) }
									items={ getModules().map( ( m ) => ( {
										label: m.label,
										value: String( m.value ),
									} ) ) }
									disabled={ disabled }
								/>
								&nbsp;
								<input
									className="button-primary"
									type="submit"
									name="add"
									value="Add"
									disabled={ disabled || name === '' }
								/>
							</td>
						</tr>
					</tbody>
				</table>

				{ moduleId === 2 && (
					<p>
						{ __(
							'Note that you will need to set the Apache module path in your Redirection options.',
							'redirection'
						) }
					</p>
				) }
			</form>
		</>
	);
}

export default CreateGroup;
