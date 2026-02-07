interface ListItem {
	value: string | number;
	label: string;
}

export default function getMatchType( type: string, list: ListItem[] ): string {
	const found = list.find( ( item ) => item.value === type );

	return found ? found.label : '-';
}
