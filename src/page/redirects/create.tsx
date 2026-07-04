import { __ } from '@wordpress/i18n';
import clsx from 'clsx';
import { has_capability, CAP_REDIRECT_ADD } from 'lib/capabilities';
import EditRedirect from 'component/redirect-edit';
import { getDefaultItem } from 'lib/redirect-constants';

interface CreateRedirectProps {
	addTop?: boolean;
	defaultFlags?: any;
}

function CreateRedirect( props: CreateRedirectProps ) {
	const { addTop } = props;
	const classes = clsx( {
		'add-new': true,
		edit: true,
		addTop,
	} );

	return (
		<>
			{ ! addTop && has_capability( CAP_REDIRECT_ADD ) && <h2>{ __( 'Add redirect', 'redirection' ) }</h2> }

			<div className={ classes }>
				<EditRedirect
					item={ getDefaultItem( '', 0, props.defaultFlags ) }
					saveButton={ __( 'Add redirect', 'redirection' ) }
					// Auto-focus is intentional when adding via the header shortcut.
					// eslint-disable-next-line jsx-a11y/no-autofocus
					autoFocus={ !! addTop }
				/>
			</div>
		</>
	);
}

export default CreateRedirect;
