import { isEnabled } from './utils';

interface ColumnProps {
	enabled?: string;
	className?: string | null;
	children: React.ReactNode;
	selected: string[];
}

const Column = ( { enabled = 'true', className = null, children, selected }: ColumnProps ) => {
	if ( isEnabled( selected, enabled ) ) {
		return <td className={ className ?? undefined }>{ children }</td>;
	}

	return null;
};

export default Column;
