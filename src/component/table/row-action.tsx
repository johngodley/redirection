import { has_capability } from 'lib/capabilities';

interface RowActionsProps {
	actions: React.ReactElement[];
	disabled?: boolean;
}

export function RowActions( props: RowActionsProps ) {
	const { actions, disabled = false } = props;

	return (
		<div className="row-actions">
			{ disabled ? (
				<span>&nbsp;</span>
			) : (
				actions.length > 0 &&
				actions
					.filter( ( item ) => item )
					.reduce< React.ReactNode[] >( ( prev, curr ) => [ ...prev, ' | ', curr ], [] )
					.slice( 1 )
			) }
		</div>
	);
}

interface RowActionProps {
	onClick?: () => void;
	children: React.ReactNode;
	href?: string;
	capability?: string;
}

export function RowAction( props: RowActionProps ) {
	const { onClick, children, href = '', capability = '' } = props;

	function click( ev: React.MouseEvent< HTMLAnchorElement > ) {
		if ( onClick ) {
			ev.preventDefault();
			onClick();
		}
	}

	if ( capability && ! has_capability( capability ) ) {
		return null;
	}

	return (
		<a href={ href ? href : '#' } onClick={ click }>
			{ children }
		</a>
	);
}
