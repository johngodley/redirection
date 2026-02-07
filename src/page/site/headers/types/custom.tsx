interface HeaderCustomProps {
	headerValue: string;
	headerName: string;
	onChange: ( value: { [ key: string ]: string } ) => void;
}

const HeaderCustom = ( { headerValue, headerName, onChange }: HeaderCustomProps ) => {
	return (
		<>
			<input
				type="text"
				className="regular-text"
				name="headerName"
				value={ headerName }
				onChange={ ( ev ) => onChange( { [ ev.target.name ]: ev.target.value } ) }
			/>
			Value:{ ' ' }
			<input
				type="text"
				className="regular-text"
				name="headerValue"
				value={ headerValue }
				onChange={ ( ev ) => onChange( { [ ev.target.name ]: ev.target.value } ) }
			/>
		</>
	);
};

export default HeaderCustom;
