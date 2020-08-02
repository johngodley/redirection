/**
 * External dependencies
 */

import React, { useState } from 'react';
import { translate as __ } from 'lib/locale';

/**
 * Internal dependencies
 */

import { RowAction } from 'component/table/row-action';
import Modal from 'wp-plugin-components/modal';
import RequestData from 'component/request-data';

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
			<RowAction onClick={ () => setShowModal( true ) }>{ __( 'View Data' ) }</RowAction>

			{ showModal && (
				<Modal onClose={ () => setShowModal( false ) }>
					<RequestData data={ data } />
				</Modal>
			) }
		</>
	);
}

export default ExtraDataAction;
