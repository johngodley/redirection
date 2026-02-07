import { __ } from '@wordpress/i18n';

interface HeaderPlainValueProps {
	headerValue: string;
	onChange: ( value: { [ key: string ]: string } ) => void;
}

const HeaderPlainValue = ( { headerValue, onChange }: HeaderPlainValueProps ) => {
	return (
		<p>
			<label htmlFor="header-plain-value">{ __( 'Value', 'redirection' ) }:</label>{ ' ' }
			<input
				id="header-plain-value"
				type="text"
				className="regular-text"
				name="headerValue"
				value={ headerValue }
				onChange={ ( ev ) => onChange( { [ ev.target.name ]: ev.target.value } ) }
			/>
		</p>
	);
};

export default HeaderPlainValue;
