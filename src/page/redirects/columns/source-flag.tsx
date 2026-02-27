import clsx from 'clsx';
import Badge from '@wp-plugin-components/badge';

interface RedirectFlagProps {
	name: string;
	className?: string;
	strikethrough?: boolean;
}

function RedirectFlag( { name, className, strikethrough }: RedirectFlagProps ) {
	return (
		<Badge className={ clsx( 'redirect-source__flag', className, { 'redirect-source__flag--disabled': strikethrough } ) }>
			{ strikethrough ? <s>{ name }</s> : name }
		</Badge>
	);
}

export default RedirectFlag;
