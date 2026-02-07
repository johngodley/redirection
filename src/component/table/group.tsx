import { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { Select } from '@wp-plugin-components';

interface Option {
	label: string;
	value: string;
}

interface TableGroupProps {
	selected: string;
	options: Option[];
	isEnabled: boolean;
	onGroup: ( group: string ) => void;
}

function TableGroup( { selected: initialSelected, options, isEnabled, onGroup }: TableGroupProps ) {
	const [ selected, setSelected ] = useState( initialSelected );

	const onChange = ( ev: React.ChangeEvent< HTMLSelectElement > ) => {
		setSelected( ev.target.value );
	};

	const onSubmit = () => {
		onGroup( selected );
	};

	return (
		<div className="alignleft actions">
			<Select items={ options } value={ selected } name="filter" onChange={ onChange } disabled={ ! isEnabled } />

			<button className="button" onClick={ onSubmit } disabled={ ! isEnabled }>
				{ __( 'Apply', 'redirection' ) }
			</button>
		</div>
	);
}

export default TableGroup;
