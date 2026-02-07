import { __ } from '@wordpress/i18n';

import TableRow from '../table-row';
import TargetUrl from '../target';

interface ActionUrlProps {
	onChange: (
		ev: React.ChangeEvent< HTMLInputElement > | { target: { name: string; value: string; type: string } }
	) => void;
	data: {
		url?: string;
	};
}

const ActionUrl = ( { onChange, data }: ActionUrlProps ) => {
	const { url } = data;

	return (
		<TableRow title={ __( 'Target URL', 'redirection' ) } className="redirect-edit__target">
			<TargetUrl
				{ ...( url !== undefined ? { url } : {} ) }
				onChange={ ( value: string ) => onChange( { target: { name: 'url', value, type: 'input' } } ) }
			/>
		</TableRow>
	);
};

export default ActionUrl;
