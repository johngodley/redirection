import { useState } from 'react';
import { __ } from '@wordpress/i18n';

interface IdName {
	id: string;
	name: string;
}

interface BulkActionsProps {
	bulk: IdName[];
	disabled: boolean;
	onBulk: ( action: string ) => void;
}

function BulkActions( props: BulkActionsProps ) {
	const { bulk, disabled, onBulk } = props;
	const [ action, setAction ] = useState( '-1' );

	function doAction() {
		if ( parseInt( action, 10 ) !== -1 ) {
			onBulk( action );
			setAction( '-1' );
		}
	}

	return (
		<div className="alignleft actions bulkactions">
			<select
				name="action"
				value={ action }
				disabled={ disabled }
				onChange={ ( ev ) => setAction( ev.target.value ) }
			>
				<option value="-1">{ __( 'Bulk Actions', 'redirection' ) }</option>

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
				{ __( 'Apply', 'redirection' ) }
			</button>
		</div>
	);
}

export default BulkActions;
