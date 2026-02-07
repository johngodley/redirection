import { nestedGroups } from 'lib/wordpress-url';
import { Select } from '@wp-plugin-components';

interface Group {
	id: number;
	name: string;
	moduleName: string;
	[ key: string ]: any;
}

interface RedirectGroupProps {
	groups: Group[];
	currentGroup: number;
	onChange: ( ev: React.ChangeEvent< HTMLSelectElement > ) => void;
}

const RedirectGroup = ( { groups, currentGroup, onChange }: RedirectGroupProps ) => {
	return (
		<Select
			name="group"
			value={ String( currentGroup ) }
			items={ nestedGroups( groups ) as any }
			onChange={ onChange }
		/>
	);
};

export default RedirectGroup;
