import { __ } from '@wordpress/i18n';

interface CheckColumnProps {
	onSelect: ( checked: boolean ) => void;
	disabled: boolean;
	selected: boolean;
}

const CheckColumn = ( props: CheckColumnProps ) => {
	const { onSelect, disabled, selected } = props;

	return (
		<th scope="col" className="manage-column column-cb check-column-red">
			<label className="screen-reader-text" htmlFor="redirection-select-all">
				{ __( 'Select All', 'redirection' ) }
			</label>
			<input
				id="redirection-select-all"
				type="checkbox"
				disabled={ disabled }
				checked={ selected }
				onChange={ ( ev ) => onSelect( ev.target.checked ) }
			/>
		</th>
	);
};

export default CheckColumn;
