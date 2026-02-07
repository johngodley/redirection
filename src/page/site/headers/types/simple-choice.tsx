import { Select } from '@wp-plugin-components';
import type { SelectOption } from '@wp-plugin-components/select';

interface HeaderSimpleChoiceProps {
	headerValue: string;
	options: SelectOption[];
	onChange: ( value: { [ key: string ]: string } ) => void;
}

const HeaderSimpleChoice = ( { headerValue, options, onChange }: HeaderSimpleChoiceProps ) => {
	return (
		<Select
			items={ options }
			name="headerValue"
			value={ headerValue }
			onChange={ ( ev ) => onChange( { [ ev.target.name ]: ev.target.value } ) }
		/>
	);
};

export default HeaderSimpleChoice;
