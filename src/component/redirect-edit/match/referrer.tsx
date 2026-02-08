import { __ } from '@wordpress/i18n';

import { ExternalLink } from '@wp-plugin-components';
import TableRow from '../table-row';

interface MatchReferrerProps {
	data: {
		referrer?: string;
		regex?: boolean;
	};
	onChange: ( ev: React.ChangeEvent< HTMLInputElement > ) => void;
}

const MatchReferrer = ( { data, onChange }: MatchReferrerProps ) => {
	const { referrer, regex } = data;

	return (
		<TableRow title={ __( 'Referrer', 'redirection' ) } className="redirect-edit__match">
			<input
				type="text"
				className="regular-text"
				name="referrer"
				value={ referrer }
				onChange={ onChange }
				placeholder={ __( 'Match against this browser referrer text', 'redirection' ) }
			/>

			<input id="redirect-referrer-regex" type="checkbox" name="regex" checked={ regex } onChange={ onChange } />
			<label className="redirect-edit-regex" htmlFor="redirect-referrer-regex">
				{ __( 'Regex', 'redirection' ) }{ ' ' }
				<sup>
					<ExternalLink url="https://redirection.me/support/redirect-regular-expressions/">?</ExternalLink>
				</sup>
			</label>
		</TableRow>
	);
};

export default MatchReferrer;
