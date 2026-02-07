import { __ } from '@wordpress/i18n';
import { getSourceFlags } from './constants';
import TableRow from './table-row';
import { MultiOptionDropdown } from '@wp-plugin-components';

type SelectedValue = string | string[];

interface UrlFlags {
	flag_case: boolean;
	flag_regex: boolean;
	flag_trailing: boolean;
}

const getUrlFlags = ( { flag_case, flag_regex, flag_trailing }: UrlFlags ): string[] =>
	[ flag_case ? 'flag_case' : null, flag_regex ? 'flag_regex' : null, flag_trailing ? 'flag_trailing' : null ].filter(
		( item ): item is string => item !== null
	);

interface RedirectSourceUrlProps {
	url: string | string[];
	flags: UrlFlags;
	defaultFlags: UrlFlags;
	onFlagChange: ( flags: UrlFlags ) => void;
	onChange: ( ev: React.ChangeEvent< HTMLInputElement > ) => void;
	autoFocus?: boolean;
}

const RedirectSourceUrl = ( { url, flags, onFlagChange, onChange, autoFocus = false }: RedirectSourceUrlProps ) => {
	const flagOptions = getSourceFlags();

	if ( Array.isArray( url ) ) {
		return (
			<TableRow title={ __( 'Source URL', 'redirection' ) } className="top">
				<textarea value={ url.join( '\n' ) } readOnly />
			</TableRow>
		);
	}

	function changeFlag( selected: SelectedValue ) {
		const selectedArray = Array.isArray( selected ) ? selected : [ selected ];
		onFlagChange( {
			flag_case: selectedArray.includes( 'flag_case' ),
			flag_trailing: selectedArray.includes( 'flag_trailing' ),
			flag_regex: selectedArray.includes( 'flag_regex' ),
		} );
	}

	return (
		<TableRow title={ __( 'Source URL', 'redirection' ) } className="redirect-edit__source">
			<input
				type="text"
				name="url"
				value={ url }
				onChange={ onChange }
				// Auto-focusing improves keyboard workflow when adding redirects.
				// eslint-disable-next-line jsx-a11y/no-autofocus
				autoFocus={ autoFocus }
				className="regular-text"
				placeholder={ __( 'The relative URL you want to redirect from', 'redirection' ) }
			/>

			<MultiOptionDropdown
				options={ flagOptions }
				selected={ getUrlFlags( flags ) }
				onChange={ changeFlag as any }
				title={ __( 'URL options / Regex', 'redirection' ) }
				badges
				multiple
				hideTitle
			/>
		</TableRow>
	);
};

export default RedirectSourceUrl;
