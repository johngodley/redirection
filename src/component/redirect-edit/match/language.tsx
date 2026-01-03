import { __ } from '@wordpress/i18n';

import TableRow from '../table-row';

interface MatchLanguageProps {
	data: {
		language?: string;
	};
	onChange: ( ev: React.ChangeEvent< HTMLInputElement > ) => void;
}

const MatchLanguage = ( { data, onChange }: MatchLanguageProps ) => {
	const { language } = data;

	return (
		<TableRow title={ __( 'Language', 'redirection' ) } className="redirect-edit__match">
			<input
				type="text"
				className="regular-text"
				name="language"
				value={ language }
				onChange={ onChange }
				placeholder={ __(
					'Comma separated list of languages to match against (i.e. da, en-GB)',
					'redirection'
				) }
			/>
		</TableRow>
	);
};

export default MatchLanguage;
