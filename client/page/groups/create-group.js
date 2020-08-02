/**
 * External dependencies
 */

import React, { useState } from 'react';
import { translate as __ } from 'wp-plugin-lib/locale';
import { connect } from 'react-redux';

/**
 * Internal dependencies
 */
import Select from 'wp-plugin-components/select';
import { getModules } from 'state/io/selector';
import { createGroup } from 'state/group/action';

function CreateGroup( props ) {
	const { disabled, onCreate } = props;
	const [ name, setName ] = useState( '' );
	const [ moduleId, setModuleId ] = useState( 1 );

	function onSubmit( ev ) {
		ev.preventDefault();
		onCreate( { id: 0, name, moduleId } );
		setName( '' );
	}

	return (
		<>
			<h2>{ __( 'Add Group' ) }</h2>
			<p>
				{ __(
					'Use groups to organise your redirects. Groups are assigned to a module, which affects how the redirects in that group work. If you are unsure then stick to the WordPress module.'
				) }
			</p>

			<form onSubmit={ onSubmit }>
				<table className="form-table redirect-groups">
					<tbody>
						<tr>
							<th>{ __( 'Name' ) }</th>
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
									value={ moduleId }
									onChange={ ( ev ) => setModuleId( parseInt( ev.target.value, 10 ) ) }
									items={ getModules() }
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
						{ __( 'Note that you will need to set the Apache module path in your Redirection options.' ) }
					</p>
				) }
			</form>
		</>
	);
}

function mapDispatchToProps( dispatch ) {
	return {
		onCreate: ( item ) => {
			dispatch( createGroup( item ) );
		},
	};
}

export default connect(
	null,
	mapDispatchToProps
)( CreateGroup );
