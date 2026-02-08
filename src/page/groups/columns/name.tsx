import Highlighter from 'react-highlight-words';

interface NameColumnProps {
	row: { enabled: boolean; name: string };
	filters: { name: string };
}

function NameColumn( { row, filters }: NameColumnProps ) {
	const { enabled, name } = row;

	if ( enabled ) {
		return <Highlighter searchWords={ [ filters.name ] } textToHighlight={ name } autoEscape />;
	}

	return <s>{ name }</s>;
}

export default NameColumn;
