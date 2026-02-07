import { Select } from '@wp-plugin-components';

interface ChoiceOption {
	value: string;
	label: string;
}

interface Options {
	choices: ChoiceOption[];
	replace: string;
	replaceType: 'uri' | 'integer' | string;
}

interface HeaderSettings {
	selected?: string;
	replaceValue?: string | number;
}

interface HeaderReplaceSimpleChoiceProps {
	headerValue: string;
	headerSettings: HeaderSettings;
	options: Options;
	onChange: ( value: { headerValue: string; headerSettings?: HeaderSettings } ) => void;
}

const getReplace = ( name: string ): string => '<' + name + '>';
const hasReplace = ( value: string, replace: string ): boolean => value.includes( getReplace( replace ) );

function validateValue( value: string, replaceType: string ): string | number {
	if ( replaceType === 'uri' ) {
		return value.replace( /[^A-Za-z0-9-._~:/?#\[\]@!$&'()*+,;=]/g, '' );
	} else if ( replaceType === 'integer' ) {
		return parseInt( value, 10 );
	}

	return value;
}

const HeaderReplaceSimpleChoice = ( {
	headerValue,
	headerSettings,
	options,
	onChange,
}: HeaderReplaceSimpleChoiceProps ) => {
	const { choices, replace, replaceType } = options;
	const selected = headerSettings.selected ? headerSettings.selected : headerValue;
	const replaceValue = headerSettings.replaceValue ? headerSettings.replaceValue : '';
	const onChangeReplace = ( ev: React.ChangeEvent< HTMLInputElement > ) => {
		onChange( {
			headerValue: selected.replace( getReplace( replace ), ev.target.value ),
			headerSettings: {
				selected,
				replaceValue: validateValue( ev.target.value, replaceType ),
			},
		} );
	};
	const onChangeHeader = ( ev: React.ChangeEvent< HTMLSelectElement > ) => {
		onChange( {
			headerValue: ev.target.value,
			headerSettings: {
				selected: ev.target.value,
				replaceValue: '',
			},
		} );
	};

	return (
		<>
			<Select items={ choices } name="headerValue" value={ selected } onChange={ onChangeHeader } />

			{ hasReplace( selected, replace ) && (
				<>
					<label htmlFor={ `header-replace-${ replace }` }>
						{ getReplace( replace ).replace( /</g, '' ).replace( />/g, '' ) }
					</label>

					<input
						id={ `header-replace-${ replace }` }
						type="text"
						className="regular-text"
						name="replaceValue"
						value={ replaceValue }
						onChange={ onChangeReplace }
					/>
				</>
			) }
		</>
	);
};

export default HeaderReplaceSimpleChoice;
