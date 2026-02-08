import { Select } from '@wp-plugin-components';
import { getActions } from './constants';
import { hasUrlTarget, MATCH_LOGIN } from 'lib/redirect-constants';

interface LabelValue {
	value: string;
	label: string;
}

interface ActionTypeProps {
	actionType: string;
	matchType: string;
	onChange: ( ev: React.ChangeEvent< HTMLSelectElement > ) => void;
}

const ActionType = ( { actionType, matchType, onChange }: ActionTypeProps ) => {
	const remover = ( item: LabelValue ) => {
		if ( matchType === MATCH_LOGIN && ! hasUrlTarget( item.value ) ) {
			return false;
		}

		return true;
	};

	return (
		<Select
			name="action_type"
			value={ actionType }
			items={ getActions().filter( remover ) }
			onChange={ onChange }
		/>
	);
};

export default ActionType;
