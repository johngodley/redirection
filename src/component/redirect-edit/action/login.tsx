import { __ } from '@wordpress/i18n';

import TableRow from '../table-row';

interface ActionLoginProps {
	onChange: ( ev: React.ChangeEvent< HTMLInputElement > ) => void;
	data: {
		logged_in?: string;
		logged_out?: string;
	};
}

const ActionLogin = ( { onChange, data }: ActionLoginProps ) => {
	const { logged_in, logged_out } = data;

	return (
		<>
			<TableRow title={ __( 'Logged In', 'redirection' ) } className="redirect-edit__target__matched">
				<input
					type="text"
					className="regular-text"
					name="logged_in"
					value={ logged_in }
					onChange={ onChange }
					placeholder={ __( 'Target URL when matched (empty to ignore)', 'redirection' ) }
				/>
			</TableRow>
			<TableRow title={ __( 'Logged Out', 'redirection' ) } className="redirect-edit__target__unmatched">
				<input
					type="text"
					className="regular-text"
					name="logged_out"
					value={ logged_out }
					onChange={ onChange }
					placeholder={ __( 'Target URL when not matched (empty to ignore)', 'redirection' ) }
				/>
			</TableRow>
		</>
	);
};

export default ActionLogin;
