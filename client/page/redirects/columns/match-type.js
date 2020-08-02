export default function getMatchType( type, list ) {
	const found = list.find( item => item.value === type );

	return found ? found.label : '-';
}
