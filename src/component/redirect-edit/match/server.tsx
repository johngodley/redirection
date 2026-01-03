import { __ } from '@wordpress/i18n';

import TableRow from '../table-row';

interface MatchServerProps {
	data: {
		server?: string;
	};
	onChange: ( ev: React.ChangeEvent< HTMLInputElement > ) => void;
}

const MatchServer = ( { data, onChange }: MatchServerProps ) => {
	const { server } = data;

	return (
		<TableRow title={ __( 'Server', 'redirection' ) } className="redirect-edit__match">
			<input
				type="text"
				className="regular-text"
				name="server"
				value={ server }
				placeholder={ __( 'Enter server URL to match against', 'redirection' ) }
				onChange={ onChange }
			/>
		</TableRow>
	);
};

export default MatchServer;
