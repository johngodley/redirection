/**
 * External dependencies
 */

import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */

import { RowAction } from '../../table/row-action';
import { Modal } from '@wp-plugin-components';
import RequestData from '../../request-data';

/**
 * Geo map row action + modal popup
 *
 * @param {object} props - Component props
 * @param {object} props.data - Extra data
 */
function ExtraDataAction( props ) {
	const { data } = props;
	const [ showModal, setShowModal ] = useState( false );

	return (
		<>
			<RowAction onClick={ () => setShowModal( true ) }>{ __( 'View Data', 'redirection' ) }</RowAction>

			{ showModal && (
				<Modal onClose={ () => setShowModal( false ) }>
					<RequestData data={ data } />
				</Modal>
			) }
		</>
	);
}

export default ExtraDataAction;
