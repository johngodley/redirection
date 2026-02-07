import { __ } from '@wordpress/i18n';

import TableRow from '../table-row';

interface ActionUrlFromProps {
	onChange: ( ev: React.ChangeEvent< HTMLInputElement > ) => void;
	data: {
		url_from?: string;
		url_notfrom?: string;
	};
}

const ActionUrlFrom = ( { onChange, data }: ActionUrlFromProps ) => {
	const { url_from, url_notfrom } = data;

	return (
		<>
			<TableRow title={ __( 'Matched Target', 'redirection' ) } className="redirect-edit__target__matched">
				<input
					type="text"
					className="regular-text"
					name="url_from"
					value={ url_from }
					onChange={ onChange }
					placeholder={ __( 'Target URL when matched (empty to ignore)', 'redirection' ) }
				/>
			</TableRow>
			<TableRow title={ __( 'Unmatched Target', 'redirection' ) } className="redirect-edit__target__unmatched">
				<input
					type="text"
					className="regular-text"
					name="url_notfrom"
					value={ url_notfrom }
					onChange={ onChange }
					placeholder={ __( 'Target URL when not matched (empty to ignore)', 'redirection' ) }
				/>
			</TableRow>
		</>
	);
};

export default ActionUrlFrom;
