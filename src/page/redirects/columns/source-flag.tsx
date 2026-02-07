import clsx from 'clsx';
import Badge from '@wp-plugin-components/badge';

interface RedirectFlagProps {
	name: string;
	className?: string;
}

function RedirectFlag( { name, className }: RedirectFlagProps ) {
	return <Badge className={ clsx( 'redirect-source__flag', className ) }>{ name }</Badge>;
}

export default RedirectFlag;
