/**
 * External dependencies
 */

import React from 'react';
import { translate as __ } from 'i18n-calypso';
import classnames from 'classnames';

/**
 * Internal dependencies
 */

import { has_capability, CAP_REDIRECT_ADD } from 'lib/capabilities';
import EditRedirect from 'component/redirect-edit';
import { getDefaultItem } from 'state/redirect/selector';

function CreateRedirect( props ) {
	const { addTop } = props;
	const classes = classnames( {
		'add-new': true,
		edit: true,
		addTop,
	} );

	return (
		<>
			{ ! addTop && has_capability( CAP_REDIRECT_ADD ) && <h2>{ __( 'Add new redirection' ) }</h2> }

			<div className={ classes }>
				<EditRedirect
					item={ getDefaultItem( '', 0, props.defaultFlags ) }
					saveButton={ __( 'Add Redirect' ) }
					autoFocus={ addTop }
				/>
			</div>
		</>
	);
}

export default CreateRedirect;
