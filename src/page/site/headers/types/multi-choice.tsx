import { __ } from '@wordpress/i18n';
import { MultiOptionDropdown } from '@wp-plugin-components';

interface ChoiceOption {
	value: string;
	label: string;
}

interface Options {
	choices: ChoiceOption[];
	implode: string;
	wildCard?: string;
}

interface HeaderMultiChoiceProps {
	headerValue: string;
	options: Options;
	onChange: ( value: { headerValue: string } ) => void;
}

const HeaderMultiChoice = ( { headerValue, options, onChange }: HeaderMultiChoiceProps ) => {
	const { choices, implode, wildCard } = options;
	const selected = headerValue.split( implode );

	const applyItem = ( newSelected: string[] | Record< string, string | boolean | undefined > ) => {
		if ( ! Array.isArray( newSelected ) ) {
			return;
		}

		const previousSelected = selected;
		const added =
			newSelected.find( ( value ) => ! previousSelected.includes( value ) ) ||
			previousSelected.find( ( value ) => ! newSelected.includes( value ) );

		if ( wildCard && added === wildCard ) {
			const isCurrentlyWildcardOnly = previousSelected.length === 1 && previousSelected[ 0 ] === wildCard;

			onChange( { headerValue: isCurrentlyWildcardOnly ? '' : wildCard } );
			return;
		}

		onChange( {
			headerValue: newSelected
				.filter( ( item ) => item !== wildCard )
				.filter( ( item ) => item )
				.join( implode ),
		} );
	};

	return (
		<MultiOptionDropdown
			options={
				wildCard ? choices.concat( [ { value: wildCard, label: __( 'All', 'redirection' ) } ] ) : choices
			}
			selected={ selected }
			onChange={ applyItem }
			title={ __( 'Values', 'redirection' ) }
			hideTitle
			multiple
			badges
		/>
	);
};

export default HeaderMultiChoice;
