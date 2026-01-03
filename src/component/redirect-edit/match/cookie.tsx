import { __ } from '@wordpress/i18n';

import { ExternalLink } from '@wp-plugin-components';
import TableRow from '../table-row';

interface MatchCookieProps {
	data: {
		name?: string;
		value?: string;
		regex?: boolean;
	};
	onChange: ( ev: React.ChangeEvent< HTMLInputElement > ) => void;
}

const MatchCookie = ( { data, onChange }: MatchCookieProps ) => {
	const { name, value, regex } = data;

	return (
		<TableRow title={ __( 'Cookie', 'redirection' ) } className="redirect-edit__match">
			<input
				type="text"
				name="name"
				value={ name }
				onChange={ onChange }
				className="regular-text"
				placeholder={ __( 'Cookie name', 'redirection' ) }
			/>
			<input
				type="text"
				name="value"
				value={ value }
				onChange={ onChange }
				className="regular-text"
				placeholder={ __( 'Cookie value', 'redirection' ) }
			/>

			<input id="redirect-cookie-regex" type="checkbox" name="regex" checked={ regex } onChange={ onChange } />
			<label className="redirect-edit-regex" htmlFor="redirect-cookie-regex">
				{ __( 'Regex', 'redirection' ) }{ ' ' }
				<sup>
					<ExternalLink url="https://redirection.me/support/redirect-regular-expressions/">?</ExternalLink>
				</sup>
			</label>
		</TableRow>
	);
};

export default MatchCookie;
