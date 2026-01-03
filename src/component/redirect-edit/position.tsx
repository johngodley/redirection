import { __ } from '@wordpress/i18n';

interface RedirectPositionProps {
	position: number | string;
	onChange: ( ev: React.ChangeEvent< HTMLInputElement > ) => void;
}

const RedirectPosition = ( { position, onChange }: RedirectPositionProps ) => {
	return (
		<span className="redirect-edit-position">
			<strong>{ __( 'Position', 'redirection' ) }</strong>
			&nbsp;
			<input type="number" value={ position } name="position" min="0" size={ 3 } onChange={ onChange } />
		</span>
	);
};

export default RedirectPosition;
